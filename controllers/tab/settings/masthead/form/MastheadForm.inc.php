<?php

/**
 * @file controllers/tab/settings/masthead/form/MastheadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 * @ingroup controllers_tab_settings_masthead_form
 *
 * @brief Form to edit press general information settings.
 */

import('lib.pkp.classes.form.Form');
import('controllers.tab.settings.form.PressSettingsForm');

class MastheadForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function MastheadForm() {
		$settings = array(
			'name' => 'string',
			'initials' => 'string',
			'description' => 'string',
			'mailingAddress' => 'string',
			'pressEnabled' => 'boolean'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/masthead/form/mastheadForm.tpl');

		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.setup.form.pressNameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'initials', 'required', 'manager.setup.form.pressInitialsRequired'));
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('name', 'initials', 'description');
	}


	//
	// Overridden methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::execute()
	 */
	function execute($request) {
		$press =& Request::getPress();

		if ($press->getEnabled() !== $this->getData('pressEnabled')) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press->setEnabled($this->getData('pressEnabled'));
			$pressDao->updatePress($press);
		}

		parent::execute();
	}
}

