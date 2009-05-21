<?php

/**
 * @file classes/manager/form/SubmissionCategoryForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCategoryForm
 * @ingroup manager_form
 *
 * @brief Form for creating and modifying press categories.
 */

// $Id$


import('manager.form.AcquisitionsArrangementForm');

class SubmissionCategoryForm extends AcquisitionsArrangementForm {

	/**
	 * Constructor.
	 * @param $pressId int omit for a new category
	 */
	function SubmissionCategoryForm($arrangementId = null) {
		parent::Form('manager/submissionCategory/submissionCategoryForm.tpl');

		$press =& Request::getPress();
		$this->acquisitionsArrangementId = $arrangementId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.categories.form.titleRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'abbrev', 'required', 'manager.categories.form.abbrevRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCustom($this, 'reviewFormId', 'optional', 'manager.categories.form.reviewFormId', array(DAORegistry::getDAO('ReviewFormDAO'), 'reviewFormExists'), array($press->getId())));

		$this->includeAcquisitionsArrangementEditor = $this->omitAcquisitionsArrangementEditor = null;

		// Get a list of acquisitions editors for this press.
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$this->acquisitionsArrangementEditors =& $roleDao->getUsersByRoleId(ROLE_ID_ACQUISITIONS_EDITOR, $press->getId());
		$this->acquisitionsArrangementEditors =& $this->acquisitionsArrangementEditors->toArray();
	}

	/**
	 * Display the form.
	 */
	function display() {
		parent::display();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId','press.managementPages.categories');
	}

}

?>