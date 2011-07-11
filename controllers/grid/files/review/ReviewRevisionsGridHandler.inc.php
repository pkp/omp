<?php

/**
 * @file controllers/grid/files/review/ReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
		// FIXME: #6244# HARDCODED INTERNAL_REVIEW
		parent::FileSignoffGridHandler(
			new ReviewRevisionsGridDataProvider(),
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'signOffFiles')
		);

		// Set the grid title.
		$this->setTitle('editor.monograph.revisions');
	}
}

?>
