<?php

/**
 * @file plugins/blocks/spotlight/SpotlightBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightBlockPlugin
 * @ingroup plugins_blocks_spotlight
 *
 * @brief Class for spotlight block plugin
 */
// location constants for spotlights
define('SPOTLIGHT_DISPLAY_MODE_ALL',	1);
define('SPOTLIGHT_DISPLAY_MODE_RANDOM',	2);

import('lib.pkp.classes.plugins.BlockPlugin');

class SpotlightBlockPlugin extends BlockPlugin {
	/**
	 * Install default settings on press creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.block.spotlight.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.spotlight.description');
	}

	/**
	 * Get the management verbs for this plugin.
	 * @return array
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.block.spotlight.settings'));
		}
		// don't call parent::getManagementVerbs() which will return null for Block plugins.
		return $verbs;
	}

	/**
	 * Define management link actions for the settings verb.
	 * @return LinkAction
	 */
	function getManagementVerbLinkAction(&$request, $verb, $defaultUrl) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'settings') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal($defaultUrl,
					$this->getDisplayName());
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}
		return null;
	}

	/**
	 * Define the management functionality for this plugin.
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent) {

		$application =& Application::getApplication();
		$request =& $application->getRequest();
		$press =& $request->getPress();

		switch ($verb) {

			case 'settings':
				$templateMgr =& TemplateManager::getManager();
				$this->import('SpotlightSettingsForm');
				$form = new SpotlightSettingsForm($this, $press);
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$message = NOTIFICATION_TYPE_SUCCESS;
						$messageParams = array('contents' => __('plugins.block.spotlight.form.saved'));
						return false;
					} else {
						 $pluginModalContent = $form->fetch($request);
					}
				} else {
					$form->initData();
					$pluginModalContent = $form->fetch($request);
				}
				return true;
			default:
				// Unknown management verb
				assert(false);
			return false;
		}
	}

	/**
	 * Get the HTML contents of the spotlight block.
	 * @param $templateMgr PKPTemplateManager
	 * @return string
	 */
	function getContents(&$templateMgr) {
		$request =& Registry::get('request');
		$press =& $request->getPress();

		// Get the Spotlights currently assigned to the sidebar
		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');

		$displayMode = $this->getSetting($press->getId(), 'displayMode');
		if ($displayMode == SPOTLIGHT_DISPLAY_MODE_RANDOM) {
			$spotlight =& $spotlightDao->getRandomByLocationAndPressId(SPOTLIGHT_LOCATION_SIDEBAR, $press->getId());
			$templateMgr->assign('spotlights', array($spotlight));
		} else {
			$spotlights =& $spotlightDao->getByLocationAndPressId(SPOTLIGHT_LOCATION_SIDEBAR, $press->getId());
			$templateMgr->assign('spotlights', $spotlights->toArray());
		}
		return parent::getContents($templateMgr);
	}
}

?>
