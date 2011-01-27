<?php

/**
 * @file controllers/grid/files/review/EditorReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review)
 */

import('controllers.grid.files.review.ReviewFilesGridHandler');

class EditorReviewFilesGridHandler extends ReviewFilesGridHandler {

	/**
	 * Constructor
	 */
	function EditorReviewFilesGridHandler($canAdd = false, $isSelectable = false, $canManage = true) {
		parent::ReviewFilesGridHandler($canAdd, $isSelectable, true, $canManage);

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles', 'manageReviewFiles',
					 'uploadReviewFile', 'updateReviewFiles', 'deleteFile'));
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
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}
}