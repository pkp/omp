<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormElementForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementForm
 * @ingroup controllers_grid_reviewForm_form
 * @see ReviewFormElement
 *
 * @brief Form for creating and modifying Review Form elements.
 *
 */

import('lib.pkp.classes.form.Form');

class ReviewFormElementForm extends Form {

	/** @var $reviewFormId int The ID of the review form to which this element belongs */
	var $reviewFormId;

	/** @var $reviewFormElementId int The ID of the review form element being edited */
	var $reviewFormElementId;

	/** @var $reviewFormElement RevewFormElement The review form element being edited */
	var $reviewFormElement;


	/**
	 * Constructor.
	 * @param $reviewFormId int
	 * @param $reviewFormElementId int
	 */
	function ReviewFormElementForm($reviewFormId, $reviewFormElementId = null) {
		parent::Form('controllers/grid/settings/reviewForm/form/reviewFormElementForm.tpl');

		$this->reviewFormId = $reviewFormId;
		$this->reviewFormElementId = $reviewFormElementId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'question', 'required', 'manager.reviewFormElements.form.questionRequired'));
		$this->addCheck(new FormValidator($this, 'elementType', 'required', 'manager.reviewFormElements.form.elementTypeRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the names of fields for which localized data is allowed.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		return $reviewFormElementDao->getLocaleFieldNames();
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewFormId', $this->reviewFormId);
		$templateMgr->assign('reviewFormElementId', $this->reviewFormElementId);
		$templateMgr->assign_by_ref('multipleResponsesElementTypes', ReviewFormElement::getMultipleResponsesElementTypes());
		// in order to be able to search for an element in the array in the javascript function 'togglePossibleResponses':
		$templateMgr->assign('multipleResponsesElementTypesString', ';'.implode(';', ReviewFormElement::getMultipleResponsesElementTypes()).';');
		import('lib.pkp.classes.reviewForm.ReviewFormElement');
		$templateMgr->assign_by_ref('reviewFormElementTypeOptions', ReviewFormElement::getReviewFormElementTypeOptions());
		return parent::fetch($request);
	}

	/**
	 * Initialize form data from current review form.
	 */
	function initData($args, &$request) {
		if ($this->reviewFormElementId != null) {
			$press =& Request::getPress();
			$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
			$reviewFormElement =& $reviewFormElementDao->getReviewFormElement($this->reviewFormElementId);

			if ($reviewFormElement == null) {
				$this->reviewFormElementId = null;
			} else {
				$this->_data = array(
					'question' => $reviewFormElement->getQuestion(null), // Localized
					'required' => $reviewFormElement->getRequired(),
					'elementType' => $reviewFormElement->getElementType(),
					'possibleResponses' => $reviewFormElement->getPossibleResponses(null) //Localized
				);
			}
		}

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['reviewFormId'] = $args['reviewFormId'];
		$this->_data['rowId'] = isset($args['rowId']) ? $args['rowId'] : null;
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('question', 'required', 'elementType', 'possibleResponses'));
	}

	/**
	 * Save review form element.
	 * @see Form::execute()
	 */
	function execute() {
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		if ($this->reviewFormElementId != null) {
			$reviewFormElement =& $reviewFormElementDao->getReviewFormElement($this->reviewFormElementId);
		}

		if (!isset($reviewFormElement)) {
			$reviewFormElement = new ReviewFormElement();
			$reviewFormElement->setReviewFormId($this->reviewFormId);
			$reviewFormElement->setSequence(REALLY_BIG_NUMBER);
		}

		$reviewFormElement->setQuestion($this->getData('question'), null); // Localized
		$reviewFormElement->setRequired($this->getData('required') ? 1 : 0);
		$reviewFormElement->setElementType($this->getData('elementType'));

		if (in_array($this->getData('elementType'), ReviewFormElement::getMultipleResponsesElementTypes())) {
			$reviewFormElement->setPossibleResponses($this->getData('possibleResponses'), null); // Localized
		} else {
			$reviewFormElement->setPossibleResponses(null, null);
		}

		if ($reviewFormElement->getId() != null) {
			$reviewFormElementDao->deleteSetting($reviewFormElement->getId(), 'possibleResponses');
			$reviewFormElementDao->updateObject($reviewFormElement);
			$this->reviewFormElementId = $reviewFormElement->getId();
		} else {
			$this->reviewFormElementId = $reviewFormElementDao->insertObject($reviewFormElement);
			$reviewFormElementDao->resequenceReviewFormElements($this->reviewFormId);
		}
		$this->reviewFormElement = $reviewFormElement;
	}
}

?>
