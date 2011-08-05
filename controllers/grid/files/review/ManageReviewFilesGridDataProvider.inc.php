<?php

/**
 * @file controllers/grid/files/review/ManageReviewFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageReviewFilesGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to all of the review files so they may be pulled in to a round.
 */


import('controllers.grid.files.review.ReviewGridDataProvider');

class ManageReviewFilesGridDataProvider extends ReviewGridDataProvider {

	/**
	 * Constructor
	 */
	function ManageReviewFilesGridDataProvider() {
		parent::ReviewGridDataProvider(MONOGRAPH_FILE_REVIEW);
	}


	//
	// Override methods from ReviewGridDataProvider
	//
	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// We need ReviewGridDataProvider because is reads and passes along the round variable,
		// but we use the basic SubmissionFile loadData because it will load all the review files,
		// not just a single round.
		return SubmissionFileGridDataProvider::loadData();
	}
}

?>
