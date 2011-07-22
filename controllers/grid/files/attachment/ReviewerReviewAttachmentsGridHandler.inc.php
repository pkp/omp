<?php

/**
 * @filecontrollers/grid/files/attachment/ReviewerReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class ReviewerReviewAttachmentsGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerReviewAttachmentsGridHandler() {
		import('controllers.grid.files.attachment.ReviewerReviewAttachmentGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::FileListGridHandler(
			new ReviewerReviewAttachmentGridDataProvider(MONOGRAPH_FILE_REVIEW_ATTACHMENT),
			null,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_REVIEWER),
			array(
				'fetchGrid', 'fetchRow', 'downloadAllFiles'
			)
		);
	}
}

?>
