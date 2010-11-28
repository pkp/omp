<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Handle submission file grid requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import submission files grid specific classes.
import('controllers.grid.files.submissionFiles.SubmissionFilesGridRow');
import('controllers.grid.files.submissionFiles.SubmissionFilesGridCellProvider');

class SubmissionFilesGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function SubmissionFilesGridHandler() {
		parent::GridHandler();
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// We assume proper authentication by sub-classes.
		assert(is_a($monograph, 'Monograph'));
		return $monograph;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, &$cellProvider, $canAdd = true, $fileStage = null) {
		parent::initialize($request);

		// Load translations.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Add grid-level actions.
		if($canAdd) {
			$router =& $request->getRouter();
			$monograph =& $this->getMonograph();
			$actionArgs = array('gridId' => $this->getId(), 'monographId' => $monograph->getId());
			if (!is_null($fileStage)) {
				$actionArgs['fileStage'] = $fileStage;
			}
			$this->addAction(
				new LinkAction(
					'addFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, null, 'addFile', null, $actionArgs),
					'submission.addFile'
				),
				GRID_ACTION_POSITION_ABOVE
			);
		}

		// Basic columns
		$this->addColumn(new GridColumn('name',	'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow();
		return $row;
	}


	//
	// Public handler actions
	//
	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addFile($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newFile', 'true');

		// Calling editFile() with an empty file id will add a new file.
		return $this->editFile($args, $request);
	}

	/**
	 * An action to add a revision to an existing file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addRevision($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('isRevision', 'true');

		return $this->editFile($args, $request);
	}

	/**
	 * Action to edit an existing file (or a new one where the file id is null)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFile($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Assign the monograph.
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Assign the file id.
		$fileId = $request->getUserVar('fileId') ? (int)$request->getUserVar('fileId') : null;
		$templateMgr->assign('fileId', $fileId);

		// Assign the file stage.
		$fileStage = $request->getUserVar('fileStage') ? (int)$request->getUserVar('fileStage') : null;
		$templateMgr->assign('fileStage', $fileStage);

		// Assign the grid id.
		$templateMgr->assign('gridId', $this->getId());

		// Render the JSON message.
		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/submissionFiles.tpl'));
		return $json->getString();
	}

	/**
	 * Display the file upload form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function displayFileForm($args, &$request) {
		// Retrieve request parameters.
		$fileId = $request->getUserVar('fileId') ? (int)$request->getUserVar('fileId') : null;
		$fileStage = $request->getUserVar('fileStage') ? (int)$request->getUserVar('fileStage') : null;
		$isRevision = $request->getUserVar('isRevision') ? (boolean)$request->getUserVar('isRevision') : false;
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();

		// Handle the submission files upload form.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId, $fileStage, $isRevision);
		if ($fileForm->isLocaleResubmit()) {
			$fileForm->readInputData();
		} else {
			$fileForm->initData($args, $request);
		}

		// Render the JSON response.
		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function uploadFile($args, &$request) {
		$fileId = $request->getUserVar('fileId') ? $request->getUserVar('fileId'): null;
		$monographId = $request->getUserVar('monographId');
		$fileStage = $request->getUserVar('fileStage');
		$fileStage = empty($fileStage) ? MONOGRAPH_FILE_SUBMISSION: $request->getUserVar('fileStage');
		$isRevision = $request->getUserVar('isRevision');
		$isRevision = empty($isRevision) ? false: $request->getUserVar('isRevision');

		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId, $fileStage, $isRevision);
		$fileForm->readInputData();

		// Check to see if the file uploaded might be a revision to an existing file
		if(!$fileId && !$isRevision && ($fileStage == MONOGRAPH_FILE_SUBMISSION || $fileStage == MONOGRAPH_FILE_REVIEW)) {
			$possibleRevision = $fileForm->checkForRevision($monographId);
		} else $possibleRevision = false;

		if ($fileForm->validate() && ($fileId = $fileForm->uploadFile($args, $request)) ) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$router =& $request->getRouter();

			// If isRevision is set, the user is purposefully uploading a revision.
			if($isRevision) {
				list($fileId, $revision) = $this->confirmRevision($args, $request, $fileId);
			} else $revision = 1;

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign_by_ref('fileId', $fileId);

			$additionalAttributes = array(
 				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $fileId)),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $fileId)),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('monographId' => $monographId, 'fileId' => $fileId))
			);

			if ($possibleRevision && !$isRevision) {
				$additionalAttributes['possibleRevision'] = true;
				$additionalAttributes['possibleRevisionId'] = $possibleRevision;
				$additionalAttributes['revisionConfirmUrl'] = $router->url($request, null, null, 'confirmRevision', null, array('fileId' => $fileId, 'monographId' => $monographId));
			}

			$monographFile =& $monographFileDao->getMonographFile($fileId, $revision);
			$fileName = $monographFile->getOriginalFilename();

			$json = new JSON('true', Locale::translate('submission.uploadSuccessfulContinue', array('fileName' => $fileName)), 'false', $possibleRevision, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Confirm that the uploaded file is a revision.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string|array an array with file id and the new revision if
	 *  a fileId is given, otherwise a serialized JSON object to be returned
	 *  to the client.
	 */
	function confirmRevision($args, &$request, $fileId = null) {
		if($fileId) {
			// The user is going through the 'Upload revision' modal.
			$newId = (int)$fileId;
			$oldId = (int)$request->getUserVar('submissionFileId');
		} else {
			// This is being confirmed through the modal's 'Yes this is a revision' link.
			$newId = (int)$request->getUserVar('fileId');
			$oldId = (int)$request->getUserVar('existingFileId');
		}

		// Validate the file ids.
		if (!$oldId || !$newId) fatalError('Invalid file id!');

		// Retrieve the monograph files.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$existingMonographFile =& $monographFileDao->getMonographFile($oldId);
		$newMonographFile =& $monographFileDao->getMonographFile($newId); // This will become the newly revised file.

		// Make sure that the monograph files are actually
		// assigned to the authorized monograph.
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		if ($existingMonographFile->getMonographId() != $monographId
				|| $newMonographFile->getMonographId() != $monographId) fatalError('Invalid file id!');

		// Copy the monograph file type over to the new file.
		$newMonographFile->setMonographFileTypeId($existingMonographFile->getMonographFileTypeId());
		$monographFileDao->updateMonographFile($newMonographFile);

		// Assign the new file as the latest revision of the old file.
		$monographFileDao->setAsLatestRevision($newId, $oldId);

		if($fileId) {
			// Return the identification data of the new revision.
			return array($oldId, $revision);
		} else {
			// Need to reset the modal's URLs to the file id of the existing version.
			$router =& $request->getRouter();
			$additionalAttributes = array(
				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, array('monographId' => $monographId, 'fileId' => $oldId)),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('monographId' => $monographId, 'fileId' => $oldId)),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('monographId' => $monographId, 'fileId' => $oldId))
			);

			// Return the rendered JSON message.
			$json = new JSON('true', $newId, 'false', null, $additionalAttributes);
			return $json->getString();
		}
	}

	/**
	 * Edit the metadata of a submission file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editMetadata($args, &$request) {
		$fileId = $request->getUserVar('fileId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$fileType = $monographFileTypeDao->getById($monographFile->getMonographFileTypeId());
		$monographId = $monographFile->getMonographId();

		switch ($fileType->getCategory()) {
			case MONOGRAPH_FILE_CATEGORY_ARTWORK:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesArtworkMetadataForm');
				$metadataForm = new SubmissionFilesArtworkMetadataForm($fileId, $monographId);
				break;
			default:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesMetadataForm');
				$metadataForm = new SubmissionFilesMetadataForm($fileId, $monographId);
				break;
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('gridId', $this->getId());
		$templateMgr->assign('monographId', $monographId);

		if ($metadataForm->isLocaleResubmit()) {
			$metadataForm->readInputData();
		} else {
			$metadataForm->initData($args, $request);
		}

		$json = new JSON('true', $metadataForm->fetch($request));
		return $json->getString();
	}


	/**
	 * Save the metadata of a submission file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveMetadata($args, &$request) {
		$fileId = $request->getUserVar('fileId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$fileType = $monographFileTypeDao->getById($monographFile->getMonographFileTypeId());
		$monographId = $monographFile->getMonographId();

		if(isset($monographFile) && $monographFile->getLocalizedName() != '') { //Name exists, just updating it
			$isEditing = true;
		} else {
			$isEditing = false;
		}

		switch ($fileType->getCategory()) {
			// FIXME: Need a way to determine artwork file type from user-specified artwork file types
			case MONOGRAPH_FILE_CATEGORY_ARTWORK:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesArtworkMetadataForm');
				$metadataForm = new SubmissionFilesArtworkMetadataForm($fileId);
				break;
			default:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesMetadataForm');
				$metadataForm = new SubmissionFilesMetadataForm($fileId);
				break;
		}

		$metadataForm->readInputData();

		if ($metadataForm->validate()) {
			$metadataForm->execute($args, $request);
			$router =& $request->getRouter();

			$additionalAttributes = array('isEditing' => $isEditing, 'finishingUpUrl' => $router->url($request, null, null, 'finishFileSubmission', null, array('gridId' => $this->getId(), 'fileId' => $fileId, 'monographId' => $monographId)));
			$json = new JSON('true', '', 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('submission.submit.fileNameRequired'));
		}

		return $json->getString();
	}

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function finishFileSubmission($args, &$request) {
		$fileId = $request->getUserVar('fileId');
		$monographId = $request->getUserVar('monographId');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('gridId', $this->getId());

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl'));
		return $json->getString();
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function returnFileRow($args, &$request) {
		$fileId = $request->getUserVar('fileId');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);

		if($monographFile) {
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($fileId);
			$row->setData($monographFile);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$fileId = (int)$request->getUserVar('fileId');

		if($fileId) {
			import('classes.file.MonographFileManager');
			MonographFileManager::deleteFile($fileId);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}

	/**
	 * Download a file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function downloadFile($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monographId, $fileId, ($revision ? $revision : null));
	}

	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function downloadAllFiles($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monographId, $this->_data);
	}

	/**
	 * View a file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewFile($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('fileRevision');

		import('classes.file.MonographFileManager');
		MonographFileManager::viewFile($monographId, $fileId, ($revision ? $revision : null));
	}


	//
	// Protected helper methods
	//
	/**
	 * Function that can be called by sub-classes to load the
	 * files into the grid.
	 * @param $type integer
	 */
	function loadMonographFiles($type = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monograph =& $this->getMonograph();
		$monographFiles =& $monographFileDao->getByMonographId($monograph->getId(), $type);
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}
}