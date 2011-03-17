<?php

/**
 * @file controllers/grid/files/SelectableReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display the file revisions authors have uploaded in a selectable grid.
 *   Used for selecting files to send to external review or copyediting.
 */

// import submission files grid specific classes
import('controllers.grid.files.review.ReviewRevisionsGridHandler');

class SelectableReviewRevisionsGridHandler extends ReviewRevisionsGridHandler {
	/**
	 * Constructor
	 */
	function SelectableReviewRevisionsGridHandler() {
		parent::ReviewRevisionsGridHandler(false, true);
	}
}

?>
