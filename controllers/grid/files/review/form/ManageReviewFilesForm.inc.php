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

import('lib.pkp.classes.form.Form');

class ManageReviewFilesForm extends Form {
	/** @var int **/
	var $_monographId;

	/** @var int **/
	var $_stageId;

	/** @var int **/
	var $_round;


	/**
	 * Constructor.
	 */
	function ManageReviewFilesForm($monographId, $stageId, $round) {
		parent::Form('controllers/grid/files/review/manageReviewFiles.tpl');
		$this->_monographId = (int)$monographId;
		$this->_stageId = (int)$stageId;
		$this->_round = (int)$round;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters / Setters
	//
	/**
	 * Get the monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
	}

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
	function getRound() {
		return $this->_round;
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
		$this->setData('monographId', $this->_monographId);
		$this->setData('stageId', $this->getStageId());
		$this->setData('round', $this->getRound());
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * Save review round files
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$selectedFiles = $this->getData('selectedFiles');
		$stageId = $this->getStageId();
		$round = $this->getRound();

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($this->_monographId, MONOGRAPH_FILE_REVIEW);
		foreach ($monographFiles as $monographFile) {
			// Update the "viewable" flag accordingly.
			$monographFile->setViewable(in_array(
				$monographFile->getFileIdAndRevision(),
				$selectedFiles
			));
			$submissionFileDao->updateObject($monographFile);
		}
	}
}

?>
