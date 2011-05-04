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

import('controllers.grid.files.fileList.FileListGridHandler');

class ReviewerReviewFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerReviewFilesGridHandler() {
		import('controllers.grid.files.review.ReviewFilesGridDataProvider');
		$dataProvider = new ReviewFilesGridDataProvider();
		parent::FileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles')
		);

		// Set the grid title.
		$this->setTitle('reviewer.monograph.reviewFiles');
	}
}

?>
