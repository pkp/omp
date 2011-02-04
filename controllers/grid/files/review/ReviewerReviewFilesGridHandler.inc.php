<?php

/**
 * @file controllers/grid/files/review/ReviewerReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the reviewer review file grid (for reviewers to download files to review)
 */

import('controllers.grid.files.review.ReviewFilesGridHandler');

class ReviewerReviewFilesGridHandler extends ReviewFilesGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerReviewFilesGridHandler() {
		parent::ReviewFilesGridHandler(false, false, true, false);

		$this->addRoleAssignment(ROLE_ID_REVIEWER, array('fetchGrid', 'downloadFile', 'downloadAllFiles'));
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
}