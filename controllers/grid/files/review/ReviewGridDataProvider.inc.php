<?php

/**
 * @file controllers/grid/files/review/ReviewGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class ReviewGridDataProvider extends SubmissionFilesGridDataProvider {

	/** @var $_viewableOnly boolean */
	var $_viewableOnly;


	/**
	 * Constructor
	 */
	function ReviewGridDataProvider($fileStageId, $viewableOnly = false) {
		$this->_viewableOnly = $viewableOnly;
		parent::SubmissionFilesGridDataProvider($fileStageId);
	}


	//
	// Getters and setters
	//
	function setViewableOnly($viewableOnly) {
		$this->_viewableOnly = $viewableOnly;
	}

	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// Get the parent authorization policy.
		$policy = parent::getAuthorizationPolicy($request, $args, $roleAssignments);

		// Add policy to ensure there is a review round id.
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$policy->addPolicy(new ReviewRoundRequiredPolicy($request, $args));

		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$reviewRound = $this->getReviewRound();
		return array_merge(parent::getRequestArgs(), array(
			'reviewRoundId' => $reviewRound->getId()
			)
		);
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
		$reviewRound =& $this->getReviewRound();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getRevisionsByReviewRound($reviewRound, $this->getFileStage());
		$data = $this->prepareSubmissionFileData($monographFiles, $this->_viewableOnly);

		return $data;
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
			&$request, $this->getReviewRound(),
			__('editor.monograph.review.manageReviewFiles')
		);
		return $selectAction;
	}

	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$reviewRound =& $this->getReviewRound();

		$addFileAction = new AddFileLinkAction(
			$request, $monograph->getId(), $this->_getStageId(),
			$this->getUploaderRoles(), $this->getFileStage(),
			null, null, $reviewRound->getId()
		);
		return $addFileAction;
	}

	/**
	 * Get the review round object.
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
		return $reviewRound;
	}
}

?>
