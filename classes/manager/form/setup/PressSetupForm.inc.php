<?php

/**
 * @defgroup manager_form_setup
 */

/**
 * @file classes/manager/form/setup/PressSetupForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupForm
 * @ingroup manager_form_setup
 *
 * @brief Base class for press setup forms.
 */


import('lib.pkp.classes.form.Form');

class PressSetupForm extends Form {
	var $step;
	var $settings;

	/**
	 * Constructor.
	 * @param $step the step number
	 * @param $settings an associative array with the setting names as keys and associated types as values
	 */
	function PressSetupForm($step, $settings) {
		parent::Form(sprintf('manager/setup/step%d.tpl', $step));
		$this->addCheck(new FormValidatorPost($this));
		$this->step = $step;
		$this->settings = $settings;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('setupStep', $this->step);
		$templateMgr->assign('helpTopicId', 'press.managementPages.setup');
		$templateMgr->assign('validateId', 'setupForm');
		$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
		parent::display();
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
