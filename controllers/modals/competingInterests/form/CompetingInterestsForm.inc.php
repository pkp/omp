<?php

/**
 * @file controllers/modals/competingInterests/form/CompetingInterestsForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CompetingInterestsForm
 * @ingroup controllers_modal_competingInterests_form
 *
 * @brief Displays the press' competing interests policy
 */

import('lib.pkp.classes.form.Form');

class CompetingInterestsForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_pressId;

	/**
	 * Constructor.
	 */
	function CompetingInterestsForm($pressId) {
		parent::Form('controllers/modals/competingInterests/form/competingInterests.tpl');
		$this->_pressId = (int) $pressId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Press
	 * @return object Press
	 */
	function getPress() {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		return $pressDao->getPress($this->_pressId);
	}

	//
	// Template methods from Form
	//
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('press', $this->getPress());

		return parent::fetch($request);
	}
}

?>
