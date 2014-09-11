<?php

/**
 * @file plugins/generic/customBlockManager/CustomBlockManagerPlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.customBlockManager
 * @class CustomBlockManagerPlugin
 *
 * Plugin to let press managers add and delete sidebar blocks
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CustomBlockManagerPlugin extends GenericPlugin {
	/**
	 * @see Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.customBlockManager.displayName');
	}

	/**
	 * @see Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.customBlockManager.description');
	}

	/**
	 * @see Plugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
			if ( $this->getEnabled() ) {
				HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Register as a block plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a block plugin, i.e. to
	 * have layout tasks performed on it.
	 * @param $hookName string
	 * @param $args array
	 */
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];
		$request =& $this->getRequest();
		switch ($category) {
			case 'blocks':
				$this->import('CustomBlockPlugin');

				$press = $request->getPress();
				if (!$press) return false;

				$blocks = $this->getSetting($press->getId(), 'blocks');
				if (!is_array($blocks)) break;
				$i=0;
				foreach ($blocks as $block) {
					$blockPlugin = new CustomBlockPlugin($block, $this->getName());

					// default the block to being enabled
					if ($blockPlugin->getEnabled() !== false) {
						$blockPlugin->setEnabled(true);
					}
					// default the block to the right sidebar
					if (!is_numeric($blockPlugin->getBlockContext())) {
						$blockPlugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
					}
					$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath() . $i] =& $blockPlugin;

					$i++;
					unset($blockPlugin);
				}
				break;
		}
		return false;
	}

	/**
	 * Set up the pixel tags grid handler.
	 * @param $hookName string
	 * @param $params array
	 */
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.customBlockManager.controllers.grid.CustomBlockGridHandler') {
			define('CUSTOMBLOCKMANAGER_PLUGIN_NAME', $this->getName());
			return true;
		}
		return false;
	}

	/**
	 * @see GenericPlugin::getManagementVerbs()
	 */
	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('manageCustomBlocks', __('plugins.generic.customBlockManager.manage'));
		}
		return $verbs;
	}

	/**
	 * @see Plugin::getManagementVerbLinkAction()
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'manageCustomBlocks') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal(
					$router->url($request, null, null, 'plugin', null, array('verb' => 'manageCustomBlocks', 'plugin' => $this->getName(), 'category' => 'generic')),
					$this->getDisplayName()
			);
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}

	/**
	 * @see GenericPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		if (!parent::manage($verb, $args, $message, $messageParams)) return false;
		$request =& $this->getRequest();
		switch ($verb) {
			case 'manageCustomBlocks':
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
				import('lib.pkp.classes.form.Form');
				$form = new Form($this->getTemplatePath() . 'customBlockManager.tpl');
				$pluginModalContent = $form->fetch($request);
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
}

?>
