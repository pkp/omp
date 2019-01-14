<?php

/**
 * @file plugins/blocks/browse/BrowseBlockSettingsForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BrowseBlockSettingsForm
 * @ingroup plugins_blocks_browse
 *
 * @brief Form for press managers to setup Browse block plugin
 */


import('lib.pkp.classes.form.Form');

class BrowseBlockSettingsForm extends Form {

	//
	// Private properties
	//
	/** @var int press ID */
	var $_pressId;

	/** @var BrowseBlockPlugin Browse block plugin */
	var $_plugin;


	//
	// Constructor
	//
	/**
	 * Constructor
	 * @param $plugin BrowseBlockPlugin
	 * @param $pressId int
	 */
	function __construct($plugin, $pressId) {
		$this->setPressId($pressId);
		$this->setPlugin($plugin);

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));

		$this->setData('pluginName', $plugin->getName());
		$this->setData('pluginJavaScriptPath', $plugin->getPluginPath());
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Press ID.
	 * @return int
	 */
	function getPressId() {
		return $this->_pressId;
	}

	/**
	 * Set the Press ID.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		$this->_pressId = $pressId;
	}

	/**
	 * Get the plugin.
	 * @return BrowseBlockPlugin
	 */
	function getPlugin() {
		return $this->_plugin;
	}

	/**
	 * Set the plugin.
	 * @param $plugin BrowseBlockPlugin
	 */
	function setPlugin($plugin) {
		$this->_plugin = $plugin;
	}

	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData() {
		$pressId = $this->getPressId();
		$plugin = $this->getPlugin();
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$this->setData($fieldName, $plugin->getSetting($pressId, $fieldName));
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->_getFormFields()));
	}

	/**
	 * @see Form::execute()
	 */
	function execute() {
		$plugin = $this->getPlugin();
		$pressId = $this->getPressId();
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$plugin->updateSetting($pressId, $fieldName, $this->getData($fieldName), $fieldType);
		}
	}

	//
	// Private helper methods
	//
	function _getFormFields() {
		return array(
			'browseNewReleases' => 'bool',
			'browseCategories' => 'bool',
			'browseSeries' => 'bool',
		);
	}
}


