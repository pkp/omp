<?php

/**
 * @file controllers/grid/files/review/ReviewCategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridCategoryDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for category grids.
 */


import('controllers.grid.files.SubmissionFilesCategoryGridDataProvider');

class ReviewCategoryGridDataProvider extends SubmissionFilesCategoryGridDataProvider {

	/**
	 * Constructor
	 * @param $fileStage int
	 * @param $viewableOnly int Will be passed to the review grid data provider.
	 * See parameter description there.
	 */
	function ReviewCategoryGridDataProvider($fileStage, $viewableOnly = false) {
		parent::SubmissionFilesCategoryGridDataProvider($fileStage, array('viewableOnly' => $viewableOnly));
	}


	//
	// Getters and setters.
	//
	/**
	* @return ReviewRound
	*/
	function &getReviewRound() {
		$gridDataProvider =& $this->getGridDataProvider();
		return $gridDataProvider->getReviewRound();
	}


	//
	// Overriden public methods from SubmissionFilesCategoryGridDataProvider
	//
	/**
	 * @see SubmissionFilesCategoryGridDataProvider::getCategoryData()
	 */
	function &getCategoryData($categoryDataElement) {
		$reviewRound =& $this->getReviewRound();
		return parent::getCategoryData($categoryDataElement, $reviewRound);
	}

	/**
	 * @see SubmissionFilesCategoryGridDataProvider::initGridDataProvider()
	 */
	function &initGridDataProvider($fileStage, $initParams) {
		// This category grid data provider will use almost all the
		// same implementation of the ReviewGridDataProvider.
		import('controllers.grid.files.review.ReviewGridDataProvider');
		$reviewFilesGridDataProvider =& new ReviewGridDataProvider($fileStage);
		$reviewFilesGridDataProvider->setViewableOnly($initParams['viewableOnly']);

		return $reviewFilesGridDataProvider;
	}


	//
	// Public methods
	//
	/**
	 * @see ReviewGridDataProvider::getSelectAction()
	 */
	function &getSelectAction($request) {
		$gridDataProvider =& $this->getGridDataProvider();
		return $gridDataProvider->getSelectAction($request);
	}
}

?>
