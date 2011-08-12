<?php

/**
 * @file controllers/grid/files/attachment/EditorReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Editor's view of the Review Attachments Grid.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class EditorReviewAttachmentsGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function EditorReviewAttachmentsGridHandler() {
		import('controllers.grid.files.attachment.ReviewerReviewAttachmentGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::FileListGridHandler(
			new ReviewerReviewAttachmentGridDataProvider(MONOGRAPH_FILE_REVIEW_ATTACHMENT),
			null,
			FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array(
				'fetchGrid', 'fetchRow', 'downloadAllFiles'
			)
		);
	}
}

?>
