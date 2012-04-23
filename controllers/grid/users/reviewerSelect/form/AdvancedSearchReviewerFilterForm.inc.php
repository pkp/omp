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
			'doneMin',
			'doneMax',
			'avgMin',
			'avgMax',
			'lastMin',
			'lastMax',
			'activeMin',
			'activeMax',
			'keywords')
		);

		$keywords = $this->getData('keywords');
		if (is_array($keywords) && array_key_exists('interests', $keywords)) {
			$interests = $keywords['interests'];
			if ($interests != null && is_array($interests)) {
				// The interests are coming in encoded -- Decode them for DB storage
				$this->setData('interestSearchKeywords', array_map('urldecode', $interests));
			}
		}
		parent::readInputData();
	}

	/**
	 * Get the filter's data in an array to send back to the grid
	 * @return array
	 */
	function getFilterSelectionData() {
		$reviewerValues = array(
			'doneMin' => (int) $this->getData('doneMin'),
			'doneMax' => (int) $this->getData('doneMax'),
			'avgMin' => (int) $this->getData('avgMin'),
			'avgMax' => (int) $this->getData('avgMax'),
			'lastMin' => (int) $this->getData('lastMin'),
			'lastMax' => (int) $this->getData('lastMax'),
			'activeMin' => (int) $this->getData('activeMin'),
			'activeMax' => (int) $this->getData('activeMax')
		);

		return $filterSelectionData = array(
			'reviewerValues' => $reviewerValues,
			'interestSearchKeywords' => $this->getData('interestSearchKeywords')
		);
	}
}

?>
