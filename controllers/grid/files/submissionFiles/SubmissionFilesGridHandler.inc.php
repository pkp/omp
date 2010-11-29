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

// Import monograph file class (which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

class SubmissionFilesGridHandler extends GridHandler {
	/** @var integer */
	var $_fileStage;

	/** @var boolean */
	var $_canAdd;

	/**
	 * Constructor
	 * @param $fileStage integer the workflow stage
	 *  file storage that this grid operates on. One of
	 *  the MONOGRAPH_FILE_* constants.
	 * @param $canAdd boolen whether the grid will contain
	 *  an "add file" button.
	 * @param $revisionOnly boolean whether this grid
	 *  allows uploading of revisions only or whether also
	 *  new files can be uploaded.
	 */
	function SubmissionFilesGridHandler($fileStage, $canAdd = true, $revisionOnly = false) {
		assert(is_numeric($fileStage) && $fileStage > 0);
		$this->_fileStage = (int)$fileStage;
		$this->_canAdd = (boolean)$canAdd;

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

	/**
	 * Get the workflow stage file storage that this
	 * grid operaties on. One of the MONOGRAPH_FILE_*
	 * constants.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * Does this grid have an "add file" button?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_canAdd;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, &$cellProvider) {
		parent::initialize($request);

		// Load translations.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Add grid-level actions.
		if($this->canAdd()) {
			$router =& $request->getRouter();
			$monograph =& $this->getMonograph();
			$actionArgs = array('monographId' => $monograph->getId());
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
	 * An action to add a revision to an existing file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addRevision($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Tell the view that we want to upload a revision.
		$templateMgr->assign('isRevision', true);

		// We may also already have a specific file to add a revision to.
		$revisedFileId = $request->getUserVar('revisedFileId') ? (int)$request->getUserVar('revisedFileId') : null;
		$templateMgr->assign('revisedFileId', $revisedFileId);

		// Let "add file" handle the remainder of the use case.
		return $this->addFile($args, $request);
	}

	/**
	 * An action to add a new file or revision
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addFile($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Assign the monograph id.
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

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
		// Retrieve the authorized monograph.
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();

		// Do we upload revisions or new files?
		$isRevision = (boolean)$request->getUserVar('isRevision');

		// The revised file will be non-zero if the user selected
		// an existing file to be revised.
		if ($isRevision) {
			$revisedFileId = $request->getUserVar('revisedFileId') ? (int)$request->getUserVar('revisedFileId') : null;
		} else {
			$revisedFileId = null;
		}

		// Handle the submission files upload form.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($request, $monographId, $this->getFileStage(), $isRevision, $revisedFileId);
		$fileForm->initData($args, $request);

		// Render the JSON response.
		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function uploadFile($args, &$request) {
		// Instantiate the file form.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$monograph =& $this->getMonograph();
		$fileForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage());
		$fileForm->readInputData();

		// Validate the file and upload it.
		if ($fileForm->validate()) {
			return $fileForm->execute($request);
		} else {
			$json = new JSON('false', Locale::translate('submission.upload.invalidData'));
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
		$fileId = (int)$request->getUserVar('fileId');
		$revision = (int)$request->getUserVar('revision');

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
	function loadMonographFiles() {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monograph =& $this->getMonograph();
		$monographFiles =& $monographFileDao->getByMonographId($monograph->getId(), $this->getFileStage());
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}
}