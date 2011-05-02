<?php

/**
 * @file controllers/tab/settings/form/PressSettingsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsForm
 * @ingroup controllers_tab_settings_form
 *
 * @brief Base class for forms that manage press settings data (from press_settings table).
 */


import('lib.pkp.classes.form.Form');

class PressSettingsForm extends Form {
	var $settings;

	/**
	 * Constructor.
	 * @param $template The form template file.
	 * @param $settings An associative array with the setting names as keys and associated types as values.
	 */
	function PressSettingsForm($settings, $template) {
		$this->addCheck(new FormValidatorPost($this));
		$this->settings = $settings;
		parent::Form($template);
	}

	/**
	 * Initialize data from current settings.
	 */
	function initData() {
		$press =& Request::getPress();
		$this->_data = $press->getSettings();
	}

	/**
	 * Read user input.
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->settings));
	}

	/**
	 * Save modified settings.
	 */
	function execute() {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		foreach ($this->_data as $name => $value) {
			if (isset($this->settings[$name])) {
				$isLocalized = in_array($name, $this->getLocaleFieldNames());
				$settingsDao->updateSetting(
					$press->getId(),
					$name,
					$value,
					$this->settings[$name],
					$isLocalized
				);
			}
		}
	}
}

?>