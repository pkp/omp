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

	/** @var boolean */
	var $_revisionOnly;

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
		$this->_revisionOnly = (boolean)$revisionOnly;

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
	 * Does this grid allow the addition of files
	 * or revisions?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_canAdd;
	}

	/**
	 * Does this grid only allow revisions and no new files?
	 * @return boolean
	 */
	function revisionOnly() {
		return $this->_revisionOnly;
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
					$this->revisionOnly() ? 'submission.addRevision' : 'submission.addFile'
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
	 * An action to add a new file or revision.
	 * Displays the file upload wizard modal which in
	 * turn will request the file upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function addFile($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Assign the monograph.
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Does this upload wizard allow revisions only?
		$templateMgr->assign('revisionOnly', $this->revisionOnly());

		// Assign the pre-configured revised file id (if any).
		$revisedFileId = $this->_getRevisedFileFromRequest($request);
		$templateMgr->assign('revisedFileId', $revisedFileId);

		// Render the file upload wizard.
		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/fileFormModal.tpl'));
		return $json->getString();
	}

	/**
	 * Render the file upload wizard in its initial state.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function displayFileForm($args, &$request) {
		// Configure the submission files upload wizard.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$monograph =& $this->getMonograph();
		$revisedFileId = $this->_getRevisedFileFromRequest($request);
		$fileForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->revisionOnly(), $revisedFileId);
		$fileForm->initData($args, $request);

		// Render the wizard.
		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file and render the modified upload wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function uploadFile($args, &$request) {
		// Upload the file.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		return $this->_executeFileForm($request, FILE_FORM_UPLOAD);
	}

	/**
	 * Confirm that the uploaded file is a revision of an
	 * earlier uploaded file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function confirmRevision($args, &$request) {
		// Revise the file.
		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		return $this->_executeFileForm($request, FILE_FORM_REVISE);
	}

	/**
	 * Delete a file or revision
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$fileId = (int)$request->getUserVar('fileId');

		$success = false;
		if($fileId) {
			// Delete all revisions or only one?
			$revision = $request->getUserVar('revision')? (int)$request->getUserVar('revision') : null;

			// Delete the file/revision but only when it belongs to the authorized monograph
			// and to the right file stage.
			import('classes.file.MonographFileManager');
			$monograph =& $this->getMonograph();
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			if ($revision) {
				$success = (boolean)$submissionFileDao->deleteRevisionById($fileId, $revision, $this->getFileStage(), $monograph->getId());
			} else {
				$success = (boolean)$submissionFileDao->deleteAllRevisionsById($fileId, $this->getFileStage(), $monograph->getId());
			}
		}

		if ($success) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}

	/**
	 * Edit the metadata of the latest revision of
	 * the requested submission file.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function editMetadata($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();

		// Retrieve the latest revision of the requested monograph file.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$fileId = (int)$request->getUserVar('fileId');
		$monographFile =& $submissionFileDao->getLatestRevision($fileId, $this->getFileStage(), $monographId);

		// Validate the file.
		if (!is_a($monographFile, 'MonographFile')
				|| $monographFile->getFileStage() != $this->getFileStage()) fatalError('Invalid file id!');

		// Identify the genre category of the monograph file.
		$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
		$genre =& $genreDao->getById($monographFile->getGenreId());
		assert(is_a($genre, 'Genre'));

		// Import the meta-data form based on the genre category.
		switch ($genre->getCategory()) {
			case GENRE_CATEGORY_ARTWORK:
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
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function saveMetadata($args, &$request) {
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$fileId = $request->getUserVar('fileId');

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($fileId, $this->getFileStage(), $monographId);

		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$genre = $genreDao->getById($monographFile->getGenreId());

		if(isset($monographFile) && $monographFile->getLocalizedName() != '') { //Name exists, just updating it
			$isEditing = true;
		} else {
			$isEditing = false;
		}

		switch ($genre->getCategory()) {
			case GENRE_CATEGORY_ARTWORK:
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
	 * @param $request Request
	 * @return string a serialized JSON object
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
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function returnFileRow($args, &$request) {
		$fileId = $request->getUserVar('fileId');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($fileId);

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
	 * Download a file
	 * @param $args array
	 * @param $request Request
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
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		$monographId = (int)$request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monographId, $this->_data);
	}

	/**
	 * View a file
	 * @param $args array
	 * @param $request Request
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
	 */
	function loadMonographFiles() {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monograph =& $this->getMonograph();
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->getFileStage());
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}


	//
	// Private helper methods
	//
	/**
	 * Find out whether we got a revised file pre-configured
	 * in the request and return it's id or null.
	 * @param $request Request
	 * @return integer
	 */
	function _getRevisedFileFromRequest(&$request) {
		// The revised file will be non-zero if we revise a
		// single existing file.
		if ($this->revisionOnly()) {
			$revisedFileId = $request->getUserVar('revisedFileId') ? (int)$request->getUserVar('revisedFileId') : null;
		} else {
			$revisedFileId = null;
		}
		return $revisedFileId;
	}

	/**
	 * Execute the file upload form.
	 * @param $request Request
	 * @param $executionMode integer one of the FILE_FORM_* constants
	 * @return string a rendered JSON response
	 */
	function _executeFileForm(&$request, $executionMode) {
		// Instantiate the file form.
		$monograph =& $this->getMonograph();
		$fileForm = new SubmissionFilesUploadForm($request, $monograph->getId(), $this->getFileStage(), $this->revisionOnly());
		$fileForm->readInputData();

		// Validate the form and upload/revise the file.
		if ($fileForm->validate($request, $executionMode)) {
			if ($fileForm->execute($executionMode)) {
				// Render the updated form.
				$json = new JSON('true', $fileForm->fetch($request));
			} else {
				// Return an error.
				$json = new JSON('false', Locale::translate('common.uploadFailed'));
			}
		} else {
			$json = new JSON('false', array_pop($fileForm->getErrorsArray()));
		}
		return $json->getString();
	}
}