<?php

/**
 * @file controllers/grid/files/attachment/EditorReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachments
 *
 * @brief Handle review attachment grid requests (editor's perspective).
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class EditorReviewAttachmentsGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 */
	function EditorReviewAttachmentsGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(WORKFLOW_STAGE_ID_INTERNAL_REVIEW, MONOGRAPH_FILE_REVIEW);
		parent::SelectableFileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles')
		);

		// Set the grid title.
		$this->setTitle('grid.reviewAttachments.title');
	}


	//
	// Overridden protected methods from SelectableFileListGridHandler
	//
	/**
	 * @see SelectableFileListGridHandler::getSelectName()
	 */
	function getSelectName() {
		return 'selectedAttachments';
	}
}