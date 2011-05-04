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


// Import the base Form.
import('lib.pkp.classes.form.Form');

class PressSettingsForm extends Form {

	/** @var array */
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


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::initData()
	 */
	function initData() {
		$press =& Request::getPress();
		$this->_data = $press->getSettings();
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->settings));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		return parent::fetch(&$request);
	}

	/**
	 * @see Form::execute()
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