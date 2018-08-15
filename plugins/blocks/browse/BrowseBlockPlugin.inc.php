<?php

/**
 * @file plugins/blocks/browse/BrowseBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array_merge($actionArgs, array('verb' => 'settings'))),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $actionArgs)
		);
	}

	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($args, $request) {
		$press = $request->getPress();

		switch ($request->getUserVar('verb')) {
			case 'settings':
				$this->import('BrowseBlockSettingsForm');
				$form = new BrowseBlockSettingsForm($this, $press->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
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
			$templateMgr->assign('browseSeriesFactory', $series);
		}

		$categoriesDisplay = $this->getSetting($press->getId(), 'browseCategories');
		if ($categoriesDisplay) {
			// Provide a list of categories to browse
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$categories = $categoryDao->getByPressId($press->getId());
			$templateMgr->assign('browseCategoryFactory', $categories);
		}

		// If we're currently viewing a series or catalog, detect it
		// so that we can highlight the current selection in the
		// dropdown.
		$router = $request->getRouter();
		switch ($router->getRequestedOp($request)) {
			case 'category':
				$args = $router->getRequestedArgs($request);
				$templateMgr->assign('browseBlockSelectedCategory', reset($args));
				break;
			case 'series':
				$args = $router->getRequestedArgs($request);
				$templateMgr->assign('browseBlockSelectedSeries', reset($args));
				break;
		}

		return parent::getContents($templateMgr);
	}
}


