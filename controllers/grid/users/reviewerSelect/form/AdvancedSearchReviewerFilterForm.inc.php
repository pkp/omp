<?php

/**
 * @file controllers/grid/users/reviewer/form/AdvancedSearchReviewerFilterForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedSearchReviewerFilterForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form to filter the reviewer select grid.
 */

import('lib.pkp.classes.form.Form');

class AdvancedSearchReviewerFilterForm extends Form {
	/** @var The monograph associated with the review assignment **/
	var $_monograph;

	/** @var int */
	var $_stageId;

	/** @var int */
	var $_reviewRoundId;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $stageId int
	 * @param $reviewRoundId int
	 */
	function AdvancedSearchReviewerFilterForm($monograph, $stageId, $reviewRoundId) {
		parent::Form();
		$this->_monograph = $monograph;
		$this->_stageId = $stageId;
		$this->_reviewRoundId = $reviewRoundId;
		$this->setTemplate('controllers/grid/users/reviewer/form/advancedSearchReviewerFilterForm.tpl');
	}

	/**
	 * Get the monograph
	 * @return Object
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the stage id.
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the review round id.
	 * @return int
	 */
	function getReviewRoundId() {
		return $this->_reviewRoundId;
	}

	/*
	 * Initialize the filter form inputs
	 * @param $filterData array
	 * @param $request PKPRequest
	 */
	function initData($filterData, &$request) {
		$this->_data = $filterData;

		$monograph = $this->getMonograph();
		$this->setData('monographId', $monograph->getId());
		$this->setData('stageId', $this->getStageId());
		$this->setData('reviewRoundId', $this->getReviewRoundId());

		return parent::initData($filterData, $request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'done_min',
			'done_max',
			'avg_min',
			'avg_max',
			'last_min',
			'last_max',
			'active_min',
			'active_max')
		);

		$interests = $this->getData('interestSearchKeywords');
		if ($interests != null && is_array($interests)) {
			// The interests are coming in encoded -- Decode them for DB storage
			$this->setData('interestSearchKeywords', array_map('urldecode', $interests));
		}

		parent::readInputData();
	}

	/**
	 * Get the filter's data in an array to send back to the grid
	 * @return array
	 */
	function getFilterSelectionData() {
		$reviewerValues = array(
			'done_min' => (int) $this->getData('done_min'),
			'done_max' => (int) $this->getData('done_max'),
			'avg_min' => (int) $this->getData('avg_min'),
			'avg_max' => (int) $this->getData('avg_max'),
			'last_min' => (int) $this->getData('last_min'),
			'last_max' => (int) $this->getData('last_max'),
			'active_min' => (int) $this->getData('active_min'),
			'active_max' => (int) $this->getData('active_max')
		);

		return $filterSelectionData = array(
			'reviewerValues' => $reviewerValues,
			'interestSearchKeywords' => $this->getData('interestSearchKeywords')
		);
	}
}

?>
