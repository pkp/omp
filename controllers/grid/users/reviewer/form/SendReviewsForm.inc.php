<?php

/**
 * @file controllers/grid/users/reviewer/form/SendReviewsForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SendReviewsForm
 * @ingroup controllers_grid_reviewer_form
 *
 * @brief Form for sending reviews to an author
 */

import('lib.pkp.classes.form.Form');

class SendReviewsForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function SendReviewsForm($monographId) {
		parent::Form('controllers/grid/users/reviewer/form/sendReviewsForm.tpl');
		$this->_monographId = (int) $monographId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the MonographId
	 * @return int monographId
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the Monograph
	 * @return object monograph
	 */
	function getMonograph() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		return $monographDao->getMonograph($this->_monographId);
	}

	//
	// Template methods from Form
	//
	/**
	* Initialize form data from the associated submissionContributor.
	* @param $submissionContributor Reviewer
	*/
	function initData(&$args, &$request) {
		$monograph =& $this->getMonograph();
		$this->setData('authorName', $monograph->getAuthorString());
		$this->setData('monographId', $this->_monographId);
	}

	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getMonograph();
		$templateMgr->assign_by_ref('monograph', $monograph);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('monographId'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
	}
}

?>
