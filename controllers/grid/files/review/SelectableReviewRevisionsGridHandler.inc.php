<?php

/**
 * @file controllers/grid/files/review/SelectableReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display the file revisions authors have uploaded in a selectable grid.
 *   Used for selecting files to send to external review or copyediting.
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class SelectableReviewRevisionsGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 */
	function SelectableReviewRevisionsGridHandler() {
		import('controllers.grid.files.review.ReviewRevisionsGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::SelectableFileListGridHandler(
			new ReviewRevisionsGridDataProvider(),
			null,
			FILE_GRID_DELETE
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'fetchRow')
		);

		// Set the grid title.
		$this->setTitle('editor.monograph.revisions');

		$this->setInstructions('editor.monograph.selectPromoteRevisions');
	}
}

?>
