<?php

/**
 * @file controllers/grid/files/ReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_revisions
 *
 * @brief Display the file revisions authors have uploaded
 */

// import submission files grid specific classes
import('controllers.grid.files.review.ReviewFilesGridHandler');

class ReviewRevisionsGridHandler extends ReviewFilesGridHandler {
	/**
	 * Constructor
	 */
	function ReviewRevisionsGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = false, $canManage = false) {
		parent::ReviewFilesGridHandler($canAdd, $isSelectable, $canDownloadAll, $canManage);

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles'));
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
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		$this->setTitle('editor.monograph.revisions');
		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);
	}

	//
	// Protected methods
	//

	/**
	 * Select the files to load in the grid
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$monograph =& $this->getMonograph();
		// Grab the files that are currently set for the review
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$monographFiles =& $reviewRoundDao->getRevisionsOfCurrentReviewFiles($monograph->getId(), $this->getRound(), $this->getReviewType());

		$this->setData($monographFiles);
	}


}