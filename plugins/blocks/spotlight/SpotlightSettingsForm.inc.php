<?php

/**
 * @file plugins/generic/blocks/spotlight/SpotlightSettingsForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightSettingsForm
 * @ingroup plugins_generic_blocks_spotlight
 *
 * @brief Form for adding/editing the settings for the Spotlight block plugin
 */

import('lib.pkp.classes.form.Form');

class SpotlightSettingsForm extends Form {
	/** The press associated with the plugin being edited **/
	var $_press;

	/** The plugin being edited **/
	var $_plugin;

	/**
	 * Constructor.
	 */
	function SpotlightSettingsForm(&$plugin, &$press) {
		parent::Form($plugin->getTemplatePath() . 'settings.tpl');
		$this->setPress($press);
		$this->setPlugin($plugin);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'displayMode', 'required', 'plugins.block.spotlight.form.displayModeRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Press.
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	/**
	 * Set the Press.
	 * @param Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}

	/**
	 * Get the plugin.
	 * @return SpotlightBlockPlugin
	 */
	function getPlugin() {
		return $this->_plugin;
	}

	/**
	 * Set the plugin.
	 * @param SpotlightBlockPlugin $plugin
	 */
	function setPlugin($plugin) {
		$this->_plugin =& $plugin;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the plugin.
	 */
	function initData() {
		$plugin =& $this->getPlugin();
		$press =& $this->getPress();

		if (isset($plugin)) {
			$this->_data = array(
				'displayMode' => $plugin->getSetting($press->getId(), 'displayMode')
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$plugin =& $this->getPlugin();
		$press =& $this->getPress();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pluginName', $plugin->getName());

		$displayModes = array(
			SPOTLIGHT_DISPLAY_MODE_ALL => __('plugins.block.spotlight.form.displayModeAll'),
			SPOTLIGHT_DISPLAY_MODE_RANDOM => __('plugins.block.spotlight.form.displayModeRandom')
		);
		$templateMgr->assign('displayModes', $displayModes);

		$displayMode = $plugin->getSetting($press->getId(), 'displayMode');
		$templateMgr->assign('displayMode', isset($displayMode) ? $displayMode : SPOTLIGHT_DISPLAY_MODE_ALL);
		return $templateMgr->fetch($plugin->getTemplatePath() . 'settings.tpl');
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'displayMode'
		));
	}

	/**
	 * Save the plugin's data.
	 * @see Form::execute()
	 */
	function execute() {

		$plugin =& $this->getPlugin();
		$press =& $this->getPress();
		$plugin->updateSetting($press->getId(), 'displayMode', trim($this->getData('displayMode'), "\"\';"), 'int');
	}
}
?>
