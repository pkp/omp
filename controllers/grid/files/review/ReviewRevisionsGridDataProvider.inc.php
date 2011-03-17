<?php

/**
 * @file controllers/grid/files/review/ReviewRevisionsGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRevisionsGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review revisions (new files added during a
 *  review round) for grids.
 */


import('controllers.grid.files.review.ReviewGridDataProvider');

class ReviewRevisionsGridDataProvider extends ReviewGridDataProvider {

	/**
	 * Constructor
	 */
	function ReviewRevisionsGridDataProvider() {
		parent::ReviewGridDataProvider();
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Grab the files that are new (incoming) revisions
		// of those currently assigned to the review round.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestNewRevisionsByReviewRound($monograph->getId(), $this->_getReviewType(), $this->_getRound());
		return $this->getRevisionsByFileId($monographFiles);
	}


	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddRevisionLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddRevisionLinkAction(
			$request, $monograph->getId(), $this->getUploaderRoles(),
			$this->_getReviewType(), $this->_getRound()
		);
		return $addFileAction;
	}
}

?>
