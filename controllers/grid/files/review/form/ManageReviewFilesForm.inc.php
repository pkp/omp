<?php

/**
 * @file controllers/grid/files/review/form/ManageReviewFilesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageReviewFilesForm
 * @ingroup controllers_grid_files_review_form
 *
 * @brief Form for add or removing files from a review
 */

import('controllers.grid.files.form.ManageSubmissionFilesForm');

class ManageReviewFilesForm extends ManageSubmissionFilesForm {

	/** @var int **/
	var $_stageId;

	/** @var int **/
	var $_reviewRoundId;


	/**
	 * Constructor.
	 */
	function ManageReviewFilesForm($monographId, $stageId, $reviewRoundId) {
		parent::ManageSubmissionFilesForm($monographId, 'controllers/grid/files/review/manageReviewFiles.tpl');
		$this->_stageId = (int)$stageId;
		$this->_reviewRoundId = (int)$reviewRoundId;

	}


	//
	// Getters / Setters
	//
	/**
	 * Get the review stage id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the round
	 * @return int
	 */
	function getReviewRoundId() {
		return $this->_reviewRoundId;
	}

	/**
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->getReviewRoundById($this->getReviewRoundId());
		return $reviewRound;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize variables
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->setData('stageId', $this->getStageId());
		$this->setData('reviewRoundId', $this->getReviewRoundId());

		parent::initData($args, &$request);
	}

	/**
	 * Save review round files
	 * @param $args array
	 * @param $request PKPRequest
	 * @stageMonographFiles array The files that belongs to a file stage
	 * that is currently being used by a grid inside this form.
	 */
	function execute($args, &$request, &$stageMonographFiles) {
		parent::execute($args, $request, $stageMonographFiles, MONOGRAPH_FILE_REVIEW_FILE);
	}
}

?>
