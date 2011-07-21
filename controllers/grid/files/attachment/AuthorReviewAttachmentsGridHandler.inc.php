<?php

/**
 * @file controllers/grid/files/attachment/AuthorReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Handle review attachment grid requests (author's perspective)
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('controllers.grid.files.fileList.FileListGridHandler');

class AuthorReviewAttachmentsGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function AuthorReviewAttachmentsGridHandler() {
		import('controllers.grid.files.review.ReviewGridDataProvider');
		// FIXME: #6244# HARDCODED INTERNAL_REVIEW
		parent::FileListGridHandler(
			new ReviewGridDataProvider(WORKFLOW_STAGE_ID_INTERNAL_REVIEW, MONOGRAPH_FILE_REVIEW_ATTACHMENT),
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles')
		);

		// Set the grid title.
		$this->setTitle('grid.reviewAttachments.title');
	}
}

?>
