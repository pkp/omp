<?php

/**
 * @file controllers/grid/files/authorCopyeditingFiles/AuthorCopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorCopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_authorCopyeditingFiles
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import copyediting grid specific classes
import('controllers.grid.files.authorCopyeditingFiles.AuthorCopyeditingFilesGridCellProvider');

class AuthorCopyeditingFilesGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function AuthorCopyeditingFilesGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_REVIEWER,  ROLE_ID_PRESS_ASSISTANT), array());
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
								 array('fetchGrid', 'addCopyeditedFile', 'displayFileForm', 'uploadFile', 'editMetadata', 'saveMetadata',
								 	   'finishFileSubmission', 'returnFileRow', 'downloadFile', 'deleteFile'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$monographId = (integer)$request->getUserVar('monographId');
		$this->setId('copyeditingFiles');
		$this->setTitle('submission.copyediting');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Grab the copyediting files to display as categories
		$user =& $request->getUser();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, null, $user->getId());

		$this->setData($signoffs);

		// Grid Columns
		$cellProvider =& new AuthorCopyeditingFilesGridCellProvider();

		// Add a column for the file's label
		$this->addColumn(
			new GridColumn(
				'name',
				'common.file',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Add a column to show whether the author uploaded a copyedited version of the file
		$this->addColumn(
			new GridColumn(
				'responded',
				'submission.response',
				null,
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

	}


	//
	// Public methods
	//

	/**
	 * Show the copyedited file upload form (to add a copyedited response to a file)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addCopyeditedFile($args, &$request) {
		import('classes.monograph.MonographFile');
		$fileStage = MONOGRAPH_FILE_COPYEDIT;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $request->getUserVar('monographId'));
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

		import('controllers.grid.files.authorCopyeditingFiles.form.AuthorCopyeditingFilesUploadForm');
		$fileForm = new AuthorCopyeditingFilesUploadForm($monographId, null);

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
	 * @return string Serialized JSON Object
	 */
	function uploadFile($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();
		$signoffId = $request->getUserVar('copyeditingSignoffId');
		assert(is_numeric($signoffId));

		import('controllers.grid.files.authorCopyeditingFiles.form.AuthorCopyeditingFilesUploadForm');
		$fileForm = new AuthorCopyeditingFilesUploadForm($monographId, $signoffId);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$copyeditedFileId = $fileForm->uploadFile($args, $request);;

			$router =& $request->getRouter();
			$additionalAttributes = array(
				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $copyeditedFileId)),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $copyeditedFileId, 'signoffId' => $signoffId)),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('fileId' => $copyeditedFileId, 'signoffId' => $signoffId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $copyeditedFileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Edit the metadata of a submission file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editMetadata($args, &$request) {
		$fileId = $request->getUserVar('fileId');
		$signoffId = $request->getUserVar('signoffId');

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$fileType = $monographFileTypeDao->getById($monographFile->getMonographFileTypeId());
		$monographId = $monographFile->getMonographId();

		switch ($fileType->getCategory()) {
			// FIXME: Need a way to determine artwork file type from user-specified artwork file types
			case MONOGRAPH_FILE_CATEGORY_ARTWORK:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesArtworkMetadataForm');
				$metadataForm = new SubmissionFilesArtworkMetadataForm($fileId, $signoffId);
				break;
			default:
				import('controllers.grid.files.submissionFiles.form.SubmissionFilesMetadataForm');
				$metadataForm = new SubmissionFilesMetadataForm($fileId, $signoffId);
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

			$additionalAttributes = array('isEditing' => true, 'finishingUpUrl' => $router->url($request, null, null, 'finishFileSubmission', null, array('gridId' => $this->getId(), 'fileId' => $fileId, 'monographId' => $monographId)));
			$json = new JSON('true', '', 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('submission.submit.fileNameRequired'));
		}

		return $json->getString();
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function returnFileRow($args, &$request) {
		$signoffId = (integer)$request->getUserVar('signoffId');

		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($signoffId);

		if($signoff) {
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($signoffId);
			$row->setData($signoff);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}

	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function downloadFile($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');

		$sessionManager =& SessionManager::getManager();
		$session =& $sessionManager->getUserSession();
		$user =& $session->getUser();

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFile($fileId);
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile($args, &$request) {
		$signoffId = $request->getUserVar('signoffId');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($signoffId);

		if($fileId) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFileDao->deleteMonographFileById($signoff->getFileId());

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}
}