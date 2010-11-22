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

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.submissionFiles.SubmissionFilesGridRow');
import('controllers.grid.files.submissionFiles.SubmissionFilesGridCellProvider');

class SubmissionFilesGridHandler extends GridHandler {
	var $_monographId;

	/**
	 * Constructor
	 */
	function SubmissionFilesGridHandler() {
		parent::GridHandler();
	}

	/*
	* Configure the grid
	* @param $request PKPRequest
	*/
	function initialize(&$request) {
		parent::initialize($request);

		// Get the monograph id (has been authorized by now).
		$this->_monographId = $request->getUserVar('monographId');

		// Basic grid configuration
		$this->setTitle('submission.submit.submissionFiles');

		// Check wether to display the 'add file' grid action (true for all cases but the author's submission summary page)
		$canAdd = true;
		if($request->getUserVar('canAdd') == "false") {
			$canAdd = false;
		}

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$rowData = array();

		// Load in book files
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($this->_monographId);
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);

		// Add grid-level actions
		if($canAdd) {
			$this->addAction(
				new LinkAction(
					'addFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, null, 'addFile', null, array('gridId' => $this->getId(), 'monographId' => $this->_monographId)),
					'submission.addFile'
				),
				GRID_ACTION_POSITION_ABOVE
			);
		}

		// Columns
		$cellProvider = new SubmissionFilesGridCellProvider();
		$this->addColumn(new GridColumn('name',	'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('fileType',	'common.fileType', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return SubmissionFilesGridRow
	*/
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow();
		return $row;
	}

	//
	// Public File Grid Actions
	//
	/**
	* An action to add a new file
	* @param $args array
	* @param $request PKPRequest
	* @return string Serialized JSON object
	*/
	function addFile($args, &$request) {
		// Calling editSponsor with an empty file id will add a new file
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newFile', 'true');

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
		$fileId = $request->getUserVar('fileId') ? $request->getUserVar('fileId'): null;
		$fileStage = $request->getUserVar('fileStage') ? $request->getUserVar('fileStage') : null;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('fileStage', $fileStage);
		$templateMgr->assign('gridId', $this->getId());

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
		$fileId = $request->getUserVar('fileId') ? $request->getUserVar('fileId'): null;
		$monographId = $request->getUserVar('monographId');
		$fileStage = $request->getUserVar('fileStage') ? $request->getUserVar('fileStage') : null;
		$isRevision = $request->getUserVar('isRevision') ? $request->getUserVar('isRevision') : false;

		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId, $fileStage, $isRevision);

		if ($fileForm->isLocaleResubmit()) {
			$fileForm->readInputData();
		} else {
			$fileForm->initData($args, $request);
		}

		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * upload a file
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
			$possibleRevision = $fileForm->checkForRevision($args, &$request);
		} else $possibleRevision = false;

		if ($fileForm->validate() && ($fileId = $fileForm->uploadFile($args, $request)) ) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$router =& $request->getRouter();

			// If isRevision is set, the user is purposefully uploading a revision
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
	 * Confirm that the uploaded file is a revision
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 * @return string Serialized JSON object
	 */
	function confirmRevision($args, &$request, $fileId = null) {
		if(!$fileId) {
			// This is being confirmed through the modal's 'Yes this is a revision' link
			$newId = $request->getUserVar('fileId');
			$oldId = $request->getUserVar('existingFileId');
		} else {
			// The user is going through the 'Upload revision' modal
			$newId = $fileId;
			$oldId = $request->getUserVar('submissionFileId');
		}

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$existingMonographFile =& $monographFileDao->getMonographFile($oldId);

		// Set ID and revision of new file
		$revision = $monographFileDao->setAsLatestRevision($newId, $oldId);
		$newMonographFile =& $monographFileDao->getMonographFile($oldId); // This will get the newly revised file

		// Copy the monograph file type over to the new file
		$newMonographFile->setMonographFileTypeId($existingMonographFile->getMonographFileTypeId());
		$monographFileDao->updateMonographFile($newMonographFile);

		if($fileId) {
			return array($oldId, $revision);
		} else {
			// Need to reset the modal's URLs to the new file id
			$router =& $request->getRouter();
			$monographId = $newMonographFile->getMonographId();
			$additionalAttributes = array(
				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, array('monographId' => $monographId, 'fileId' => $newId)),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('monographId' => $monographId, 'fileId' => $newId)),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('monographId' => $monographId, 'fileId' => $newId))
			);

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

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
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
		$fileId = $request->getUserVar('fileId');
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFileDao->deleteMonographFileById($fileId);

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($press->getId());
		$monographFileManager->deleteFile($fileId);

		$json = new JSON('true');
		return $json->getString();
	}

	/**
	 * Download a file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function downloadFile($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');
		$revision = $request->getUserVar('fileRevision');

		import('classes.submission.common.Action');
		Action::viewFile($monographId, $fileId, $revision);
	}

	/**
	 * Download a file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewFile($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');
		$revision = $request->getUserVar('fileRevision');

		import('classes.submission.common.Action');
		Action::viewFile($monographId, $fileId, $revision);
	}
}