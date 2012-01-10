<?php

/**
 * @file controllers/grid/files/review/ReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display the file revisions authors have uploaded
 */

import('controllers.grid.files.fileSignoff.FileSignoffGridHandler');

class ReviewRevisionsGridHandler extends FileSignoffGridHandler {
	/**
	 * Constructor
	 */
	function ReviewRevisionsGridHandler() {
		import('controllers.grid.files.review.ReviewRevisionsGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::FileSignoffGridHandler(
			new ReviewRevisionsGridDataProvider(),
			null,
			'SIGNOFF_REVIEW_REVISION',
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(ROLE_ID_AUTHOR, $readAccess = array('fetchGrid', 'fetchRow'));
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array_merge($readAccess, array('signOffFile'))
		);

		// Set the grid title.
		$this->setTitle('editor.monograph.revisions');
	}
}

?>
