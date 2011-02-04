<?php

/**
 * @file controllers/grid/files/review/form/ManageReviewFilesForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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
	var $_reviewType;

	/** @var int **/
	var $_round;


	/**
	 * Constructor.
	 */
	function ManageReviewFilesForm($monographId, $reviewType, $round) {
		parent::Form('controllers/grid/files/review/manageReviewFiles.tpl');
		$this->setMonographId((int)$monographId);
		$this->setReviewType((int)$reviewType);
		$this->setRound((int)$round);

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters / Setters
	//
	/**
	 * Set the monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
	    $this->_monographId = $monographId;
	}

	/**
	 * Get the monograph id
	 * @return int
	 */
	function getMonographId() {
	    return $this->_monographId;
	}

	/**
	 * Set the review type
	 * @param $reviewType int
	 */
	function setReviewType($reviewType) {
	    $this->_reviewType = $reviewType;
	}

	/**
	 * Get the review type
	 * @return int
	 */
	function getReviewType() {
	    return $this->_reviewType;
	}

	/**
	 * Set the round
	 * @param $round int
	 */
	function setRound($round) {
	    $this->_round = $round;
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
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->_monographId);

		$this->setData('monographId', $this->_monographId);
		$this->setData('reviewType', $monograph->getCurrentReviewType());
		$this->setData('round', $monograph->getCurrentRound());
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
	function &execute($args, &$request) {
		$selectedFiles = $this->getData('selectedFiles');
		$reviewType = $this->getReviewType();
		$round = $this->getRound();

		$filesWithRevisions = array();
		if (!empty($selectedFiles)) {
			foreach ($selectedFiles as $selectedFile) {
				$filesWithRevisions[] = explode("-", $selectedFile);
			}
		}
		$reviewRoundDAO =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRoundDAO->setFilesForReview($this->getMonographId(), $reviewType, $round, $filesWithRevisions);

		// Return the files that are currently set for the review
		$reviewFilesByRound =& $reviewRoundDAO->getReviewFilesByRound($this->getMonographId());
		if (isset($reviewFilesByRound[$reviewType][$round])) {
			return $reviewFilesByRound[$reviewType][$round];
		} else {
			$noFiles = array();
			return $noFiles;
		}
	}
}

?>
