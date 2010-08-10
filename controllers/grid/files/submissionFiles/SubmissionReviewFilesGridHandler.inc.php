<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionReviewFilesGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle uploading files to the review files grid.
 */

// import submission files grid specific classes
import('controllers.grid.files.submissionFiles.SubmissionFilesGridHandler');

class SubmissionReviewFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function SubmissionReviewFilesGridHandler() {
		parent::SubmissionFilesGridHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'addFile', 'addRevision', 'editFile', 'displayFileForm', 'uploadFile',
			'confirmRevision', 'deleteFile', 'editMetadata', 'saveMetadata', 'finishFileSubmission',
			'returnFileRow', 'downloadFile'));
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		$this->setId('reviewFiles');

		import('controllers.grid.files.reviewFiles.ReviewFilesGridCellProvider');
		$cellProvider =& new ReviewFilesGridCellProvider();
		$this->addColumn(new GridColumn('select',
			'common.select',
			null,
			'controllers/grid/files/reviewFiles/gridRowSelectInput.tpl',
			$cellProvider)
		);


	}

	//
	// Overridden public AJAX methods from SubmissionFilesGridHandler
	//

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function finishFileSubmission(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$monographId = isset($args['monographId']) ? $args['monographId'] : null;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('gridId', $this->getId());

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/reviewFileSubmissionComplete.tpl'));
		return $json->getString();
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function returnFileRow(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		$monographId = $monographFile->getMonographId();

		if($monographFile) {
			import('controllers.grid.files.reviewFiles.ReviewFilesGridHandler');
			$reviewFilesGridHandler =& new ReviewFilesGridHandler();
			$reviewFilesGridHandler->initialize($request);

			$fileType = $bookFileTypeDao->getById($monographFile->getAssocId());

			$row =& $reviewFilesGridHandler->getRowInstance();
			$row->setId($monographFile->getFileId());
			$row->setData($monographFile);
			$row->initialize($request);

			$json = new JSON('true', $reviewFilesGridHandler->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}
}