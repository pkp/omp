<?php

/**
 * @file plugins/blocks/browse/BrowseBlockSettingsForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
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

	/**
	 * Get the press ID.
	 * @return int
	 */
	function _getPressId() {
		return $this->_pressId;
	}

	/** @var BrowseBlockPlugin Browse block plugin */
	var $_plugin;

	/**
	 * Get the plugin.
	 * @return BrowseBlockPlugin
	 */
	function &_getPlugin() {
		return $this->_plugin;
	}

	//
	// Constructor
	//
	/**
	 * Constructor
	 * @param $plugin BrowseBlockPlugin
	 * @param $pressId int
	 */
	function BrowseBlockSettingsForm(&$plugin, $pressId) {
		$this->_pressId = $pressId;
		$this->_plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidatorPost($this));

		$this->setData('pluginName', $plugin->getName());
	}

	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData() {
		$pressId = $this->_getPressId();
		$plugin =& $this->_getPlugin();
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
		$plugin =& $this->_getPlugin();
		$pressId = $this->_getPressId();
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

?>
