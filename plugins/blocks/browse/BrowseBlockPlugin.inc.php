<?php

/**
 * @file plugins/blocks/browse/BrowseBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BrowseBlockPlugin
 * @ingroup plugins_blocks_browse
 *
 * @brief Class for browse block plugin
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class BrowseBlockPlugin extends BlockPlugin {
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
		return __('plugins.block.browse.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.browse.description');
	}

	/**
	 * @copydoc PKPPlugin::getManagementVerbs()
	 */
	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.block.browse.settings'));
		}
		return $verbs;
	}

	/**
	 * @copydoc Plugin::getManagementVerbLinkAction()
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'settings') {
			// Generate a link action for the "settings" action
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal(
				$router->url($request, null, null, 'plugin', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'blocks')),
				$this->getDisplayName()
			);
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}


	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		$request = $this->getRequest();
		$press = $request->getPress();

		switch ($verb) {
			case 'settings':
				$this->import('BrowseBlockSettingsForm');
				$form = new BrowseBlockSettingsForm($this, $press->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$message = NOTIFICATION_TYPE_SUCCESS;
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
				return parent::manage($verb, $args, $message, $messageParams);
		}
	}

	/**
	 * Get the HTML contents of the browse block.
	 * @param $templateMgr PKPTemplateManager
	 * @return string
	 */
	function getContents($templateMgr, $request = null) {
		$press = $request->getPress();

		$browseNewReleases = $this->getSetting($press->getId(), 'browseNewReleases');
		$templateMgr->assign('browseNewReleases', $browseNewReleases);

		$seriesDisplay = $this->getSetting($press->getId(), 'browseSeries');
		if ($seriesDisplay) {
			// Provide a list of series to browse
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getByPressId($press->getId());
			$templateMgr->assign('browseSeries', $series);
		}

		$categoriesDisplay = $this->getSetting($press->getId(), 'browseCategories');
		if ($categoriesDisplay) {
			// Provide a list of categories to browse
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$categories = $categoryDao->getByPressId($press->getId());
			$templateMgr->assign('browseCategories', $categories);
		}

		// If we're currently viewing a series or catalog, detect it
		// so that we can highlight the current selection in the
		// dropdown.

		switch ($request->getUserVar('type') ) {
			case 'category':
				$templateMgr->assign('browseBlockSelectedCategory', $request->getUserVar('path'));
				break;
			case 'series':
				$templateMgr->assign('browseBlockSelectedSeries', $request->getUserVar('path'));
				break;
		}

		return parent::getContents($templateMgr);
	}
}

?>
