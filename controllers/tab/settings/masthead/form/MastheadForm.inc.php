<?php

/**
 * @file controllers/tab/settings/masthead/form/MastheadForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 * @ingroup controllers_tab_settings_masthead_form
 *
 * @brief Form to edit masthead settings.
 */

import('lib.pkp.classes.controllers.tab.settings.form.ContextSettingsForm');

class MastheadForm extends ContextSettingsForm {

	/**
	 * Constructor.
	 */
	function __construct($wizardMode = false) {
		$settings = array(
			'name' => 'string',
			'acronym' => 'string',
			'mailingAddress' => 'string',
			'customAboutItems' => 'object',
			'description' => 'string',
			'editorialTeam' => 'string',
			'about' => 'string',
		);

		parent::__construct($settings, 'controllers/tab/settings/masthead/form/mastheadForm.tpl', $wizardMode);

		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.setup.form.pressNameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'acronym', 'required', 'manager.setup.form.pressInitialsRequired'));
	}

	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('name', 'acronym', 'description', 'customAboutItems', 'editorialTeam', 'about');
	}

	//
	// Overridden methods from ContextSettingsForm.
	//
	/**
	 * @see ContextSettingsForm::initData.
	 * @param $request Request
	 */
	function initData($request) {
		parent::initData($request);

		$press = $request->getPress();
		$this->setData('enabled', (int)$press->getEnabled());
		if ($this->getData('acronym') == null) {
			$acronym = array();
			foreach (array_keys($this->supportedLocales) as $locale) {
				$acronym[$locale] = $press->getPath();
			}
			$this->setData('acronym', $acronym);
		}
	}
}

?>
