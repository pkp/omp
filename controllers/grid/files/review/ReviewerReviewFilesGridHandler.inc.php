<?php

/**
 * @file controllers/grid/files/review/ReviewerReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the reviewer review file grid (for reviewers to download files to review)
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class ReviewerReviewFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerReviewFilesGridHandler() {
		// Pass in null stageId to be set in initialize from request var.
		import('controllers.grid.files.review.ReviewerReviewFilesGridDataProvider');
		parent::FileListGridHandler(
			new ReviewerReviewFilesGridDataProvider(),
			null,
			FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_REVIEWER),
			array('fetchGrid', 'fetchRow')
		);

		// Set the grid title.
		$this->setTitle('reviewer.monograph.reviewFiles');
	}
}

?>
