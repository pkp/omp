<?php

/**
 * @file controllers/grid/files/review/ReviewFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFilesGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review round files for grids.
 */


import('controllers.grid.files.review.ReviewGridDataProvider');

class ReviewFilesGridDataProvider extends ReviewGridDataProvider {

	/**
	 * Constructor
	 */
	function ReviewFilesGridDataProvider() {
		parent::ReviewGridDataProvider();
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getRevisionsByReviewRound(
			$monograph->getId(),
			$this->_getStageId(), $this->_getRound()
		);
		return $this->prepareSubmissionFileData($monographFiles);
	}


	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getSelectAction()
	 */
	function &getSelectAction($request) {
		import('controllers.grid.files.fileList.linkAction.SelectReviewFilesLinkAction');
		$monograph =& $this->getMonograph();
		$selectAction = new SelectReviewFilesLinkAction(
			&$request, $monograph->getId(), $this->_getStageId(), $this->_getRound(),
			__('editor.submissionArchive.manageReviewFiles')
		);
		return $selectAction;
	}
}

?>
