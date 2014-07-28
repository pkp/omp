<?php

/**
 * @file plugins/generic/customBlockManager/controllers/grid/form/CustomBlockForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockForm
 * @ingroup controllers_grid_customBlockManager
 *
 * Form for press managers to create and modify sidebar blocks
 *
 */

import('lib.pkp.classes.form.Form');

class CustomBlockForm extends Form {
	/** @var int */
	var $contextId;

	/** @var CustomBlockPlugin */
	var $plugin;

	/** $var string */
	var $errors;

	/**
	 * Constructor
	 * @param $template string the path to the form template file
	 * @param $contextId int
	 * @param $plugin CustomBlockPlugin
	 */
	function CustomBlockForm($template, $contextId, $plugin = null) {

		parent::Form($template);

		$this->contextId = $contextId;
		$this->plugin = $plugin;

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidator($this, 'blockName', 'required', 'plugins.generic.customBlock.nameRequired'));
		//$this->addCheck(new FormValidatorLocale($this, 'blockContent', 'required', 'plugins.generic.customBlock.contentRequired'));

	}

	/**
	 * Initialize form data from current group group.
	 */
	function initData() {
		$contextId = $this->contextId;
		$plugin = $this->plugin;

		$templateMgr = TemplateManager::getManager();

		$blockName = null;
		$blockContent = null;
		if ($plugin) {
			$blockName = $plugin->getName();
			$blockContent = $plugin->getSetting($contextId, 'blockContent');
		}
		$this->setData('blockContent', $blockContent);
		$this->setData('blockName', $blockName);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('blockName', 'blockContent'));
	}

	/**
	 * Save page into DB
	 */
	function execute() {
		$plugin = $this->plugin;
		$contextId = $this->contextId;
		if (!$plugin) {
			// create a new custom block plugin
			import('plugins.generic.customBlockManager.CustomBlockPlugin');
			$plugin = new CustomBlockPlugin($this->getData('blockName'), CUSTOMBLOCKMANAGER_PLUGIN_NAME);
			// default the block to being enabled
			$plugin->setEnabled(true);
			// default the block to the right sidebar
			$plugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
			// add the custom block to the list of the custom block plugins in the custom block manager plugin
			$customBlockManagerPlugin = $plugin->getManagerPlugin();
			$blocks = $customBlockManagerPlugin->getSetting($contextId, 'blocks');
			array_push($blocks, $this->getData('blockName'));
			$customBlockManagerPlugin->updateSetting($contextId, 'blocks', $blocks);
		}
		// update custom block plugin content
		$plugin->updateSetting($contextId, 'blockContent', $this->getData('blockContent'));
	}

}
?>
