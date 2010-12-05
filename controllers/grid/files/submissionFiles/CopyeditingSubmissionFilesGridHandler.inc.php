<?php

/**
 * @file controllers/grid/files/submissionFiles/CopyeditingSubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingSubmissionFilesGridHandler
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Handle uploading files to the final draft, copyediting, and fair copy files grid.
 */

// import submission files grid specific classes
import('controllers.grid.files.submissionFiles.SubmissionFilesGridHandler');

class CopyeditingSubmissionFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingSubmissionFilesGridHandler() {
		parent::SubmissionFilesGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'addFile', 'editFile', 'displayFileForm', 'uploadFile',
			'confirmRevision', 'deleteFile', 'editMetadata', 'saveMetadata', 'finishFileSubmission',
			'returnFileRow', 'downloadFile'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Basic grid configuration
		$this->setTitle('submission.submit.submissionFiles');

		// Load monograph files.
		$this->loadMonographFiles();

		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		$this->addColumn(new GridColumn('fileType',	'common.fileType', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}


	//
	// Overridden public actions from SubmissionFilesGridHandler
	//
	/**
	 * Display the file upload form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function displayFileForm(&$args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('isEditing', true);
		return parent::displayFileForm($args, $request);
	}

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function finishFileSubmission(&$args, &$request) {
		$monographId = isset($args['monographId']) ? $args['monographId'] : null;
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($fileId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('fileId', $fileId);

		// Get the grid ID from the file type, so fileSubmissionComplete.tpl knows which grid to update
		$fileTypeToGridId = array(MONOGRAPH_FILE_FINAL => 'finalDraftFilesSelect',
									MONOGRAPH_FILE_COPYEDIT => 'copyeditingFiles',
									MONOGRAPH_FILE_FAIR_COPY => 'fairCopyFiles');
		$templateMgr->assign('gridId', $fileTypeToGridId[$monographFile->getFileStage()]);

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl'));
		return $json->getString();
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function returnFileRow(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;

		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($fileId);
		$monographId = $monographFile->getMonographId();

		if($monographFile) {
			// Get the handler path and name from the file type, so fileSubmissionComplete.tpl knows which grid to update
			$fileTypeToHandlerPath = array(MONOGRAPH_FILE_FINAL => 'controllers.grid.files.finalDraftFiles.FinalDraftFilesGridHandler',
									MONOGRAPH_FILE_COPYEDIT => 'controllers.grid.files.copyeditingFiles.CopyeditingFilesGridHandler',
									MONOGRAPH_FILE_FAIR_COPY => 'controllers.grid.files.fairCopyFiles.FairCopyFilesGridHandler');
			$fileTypeToHandlerName = array(MONOGRAPH_FILE_FINAL => 'FinalDraftFilesGridHandler',
									MONOGRAPH_FILE_COPYEDIT => 'CopyeditingFilesGridHandler',
									MONOGRAPH_FILE_FAIR_COPY => 'FairCopyFilesGridHandler');
			import($fileTypeToHandlerPath[$monographFile->getFileStage()]);
			$filesGridHandler =& new $fileTypeToHandlerName[$monographFile->getFileStage()]();
			$filesGridHandler->authorize($request, $args, $filesGridHandler->getRoleAssignments());
			if(is_a($filesGridHandler, 'FinalDraftFilesGridHandler')) {
				$filesGridHandler->setIsSelectable(true);
			}
			$filesGridHandler->initialize($request);

			if(is_a($filesGridHandler, 'CopyeditingFilesGridHandler')) {
				// If we are working with copyediting files, we need to return a category row
				$categoryRow =& $filesGridHandler->getCategoryRowInstance();
				$categoryRow->setGridId($this->getId());
				$categoryRow->setId($fileId);
				$categoryRow->setData($monographFile);
				$categoryRow->initialize($request);

				$json = new JSON('true', $filesGridHandler->_renderCategoryInternally($request, $categoryRow));
			} else {
				$row =& $filesGridHandler->getRowInstance();
				$row->setId($monographFile->getFileId());
				$row->setData($monographFile);
				$row->initialize($request);

				$json = new JSON('true', $filesGridHandler->_renderRowInternally($request, $row));
			}

		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}
}