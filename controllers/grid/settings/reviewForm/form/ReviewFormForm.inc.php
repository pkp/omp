<?php

/**
 * @file controllers/grid/settings/reviewForm/form/ReviewFormForm.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormForm
 * @ingroup controllers_grid_reviewForm_form
 * @see ReviewForm
 *
 * @brief Form for creating and modifying Review Forms.
 *
 */

import('lib.pkp.classes.form.Form');

class ReviewFormForm extends Form {

	/** @var $reviewFormId int The ID of the review form being edited */
	var $reviewFormId;

	/** @var $reviewFormId int The review form being edited */
	var $reviewForm;

	/**
	 * Constructor.
	 * @param $reviewFormId int
	 */
	function ReviewFormForm($reviewFormId = null) {
		parent::Form('controllers/grid/settings/reviewForm/form/reviewForm.tpl');

		$this->reviewFormId = $reviewFormId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.reviewForms.form.titleRequired'));
		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Get the names of fields for which localized data is allowed.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		return $reviewFormDao->getLocaleFieldNames();
	}

	/**
	 * Fetch the form.
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewFormId', $this->reviewFormId);
		return parent::fetch($request);
	}

	/**
	 * Initialize form data from current review form.
	 */
	function initData(&$args, &$request) {
		if ($this->reviewFormId != null) {
			$press =& Request::getPress();
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
			$reviewForm =& $reviewFormDao->getReviewForm($this->reviewFormId, ASSOC_TYPE_PRESS, $press->getId());

			$this->reviewForm =& $reviewForm;

			if ($reviewForm == null) {
				$this->reviewFormId = null;
			} else {
				$this->_data = array(
					'title' => $reviewForm->getTitle(null), // Localized
					'description' => $reviewForm->getDescription(null) // Localized
				);
			}
		}

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = isset($args['rowId']) ? $args['rowId'] : null;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'description'));
	}

	/**
	 * Save review form.
	 */
	function execute() {
		$press =& Request::getPress();
		$pressId = $press->getId();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

		if ($this->reviewFormId != null) {
			$reviewForm =& $reviewFormDao->getReviewForm($this->reviewFormId, ASSOC_TYPE_PRESS, $pressId);
		}

		if (!isset($reviewForm)) {
			$reviewForm = new ReviewForm();
			$reviewForm->setPressId($pressId);
			$reviewForm->setActive(0);
			$reviewForm->setSequence(REALLY_BIG_NUMBER);
		}

		$reviewForm->setTitle($this->getData('title'), null); // Localized
		$reviewForm->setDescription($this->getData('description'), null); // Localized

		if ($reviewForm->getId() != null) {
			$reviewFormDao->updateObject($reviewForm);
			$this->reviewFormId = $reviewForm->getId();
		} else {
			$this->reviewFormId = $reviewFormDao->insertObject($reviewForm);
			$reviewFormDao->resequenceReviewForms(ASSOC_TYPE_PRESS, $pressId);
		}
		$this->reviewForm = $reviewForm;
	}
}

?>
