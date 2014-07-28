<?php

/**
 * @file plugins/generic/customBlockManager/CustomBlockPlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.customBlockManager
 * @class CustomBlockPlugin
 *
 * A generic sidebar block that can be customized through the CustomBlockManagerPlugin
 *
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class CustomBlockPlugin extends BlockPlugin {
	var $blockName;

	/** @var string Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor
	 */
	function CustomBlockPlugin($blockName, $parentPluginName) {
		$this->blockName = $blockName;
		$this->parentPluginName = $parentPluginName;
		parent::BlockPlugin();
	}

	/**
	 * Get the management plugin
	 * @return object
	 */
	function &getManagerPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * @see Plugin::getName()
	 */
	function getName() {
		return $this->blockName;
	}

	/**
	 * @see Plugin::getPluginPath()
	 */
	function getPluginPath() {
		$plugin =& $this->getManagerPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * @see Plugin::getTemplatePath()
	 */
	function getTemplatePath() {
		$plugin =& $this->getManagerPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * @see Plugin::getHideManagement()
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * @see LazyLoadPlugin::getEnabled()
	 */
	function getEnabled() {
		if (!Config::getVar('general', 'installed')) return true;
		return parent::getEnabled();
	}

	/**
	 * @see Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return $this->blockName . ' ' . __('plugins.generic.customBlock.nameSuffix');
	}

	/**
	 * @see Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.customBlock.description');
	}

	/**
	 * @see BlockPlugin::getContents()
	 */
	function getContents(&$templateMgr, $request = null) {
		$press = $request->getPress();
		if (!$press) return '';

		$customBlockContent = $this->getSetting($press->getId(), 'blockContent');
		$currentLocale = AppLocale::getLocale();
		$templateMgr->assign('customBlockContent', $customBlockContent[$currentLocale]);
		return parent::getContents($templateMgr, $request);

	}

	/**
	 * @see BlockPlugin::getBlockContext()
	 */
	function getBlockContext() {
		if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_RIGHT_SIDEBAR;
		return parent::getBlockContext();
	}

	/**
	 * @see BlockPlugin::getSeq()
	 */
	function getSeq() {
		if (!Config::getVar('general', 'installed')) return 1;
		return parent::getSeq();
	}

}

?>
