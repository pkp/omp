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
	var $_settings;

	/** @var boolean */
	var $_wizardMode;


	/**
	 * Constructor.
	 * @param $template The form template file.
	 * @param $settings An associative array with the setting names as keys and associated types as values.
	 */
	function PressSettingsForm($settings, $template, $wizardMode) {
		$this->addCheck(new FormValidatorPost($this));
		$this->setSettings($settings);
		$this->setWizardMode($wizardMode);
		parent::Form($template);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get if the current form is in wizard mode (hide advanced settings).
	 * @return boolean
	 */
	function getWizardMode() {
		return $this->_wizardMode;
	}

	/**
	 * Set if the current form is in wizard mode (hide advanced settings).
	 * @param $wizardMode boolean
	 */
	function setWizardMode($wizardMode) {
		$this->_wizardMode = $wizardMode;
	}

	/**
	 * Get settings array.
	 * @return array
	 */
	function getSettings() {
		return $this->_settings;
	}

	/**
	 * Set settings array.
	 * @param $settings array
	 */
	function setSettings($settings) {
		$this->_settings = $settings;
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::initData()
	 * @param $request Request
	 */
	function initData($request) {
		$press =& $request->getPress();
		$this->_data = $press->getSettings();
		$this->setData('enabled', (int)$press->getEnabled()); 
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData($request) {
		$this->readUserVars(array_keys($this->getSettings()));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request, $params = null) {
		$templateMgr =& TemplateManager::getManager();

		// Insert the wizardMode parameter in params array to pass to template.
		$params = array_merge((array)$params, array('wizardMode' => $this->getWizardMode()));

		// Pass the parameters to template.
		foreach($params as $tplVar => $value) {
			$templateMgr->assign($tplVar, $value);
		}

		return parent::fetch(&$request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$press =& $request->getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$settings = $this->getSettings();

		foreach ($this->_data as $name => $value) {
			if (isset($settings[$name])) {
				$isLocalized = in_array($name, $this->getLocaleFieldNames());
				$settingsDao->updateSetting(
					$press->getId(),
					$name,
					$value,
					$settings[$name],
					$isLocalized
				);
			}
		}
	}
}

?>
