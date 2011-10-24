<?php

/**
 * @file plugins/blocks/help/BrowseBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 * Install default settings on journal creation.
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
	 * Get the HTML contents of the browse block.
	 * @param $templateMgr PKPTemplateManager
	 * @return string
	 */
	function getContents(&$templateMgr) {
		$request =& Registry::get('request');
		$press =& $request->getPress();

		// Provide a list of series to browse
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getByPressId($press->getId());
		$templateMgr->assign('browseSeries', $series);

		// Provide a list of categories to browse
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categories =& $categoryDao->getByPressId($press->getId());
		$templateMgr->assign('browseCategories', $categories);

		return parent::getContents($templateMgr);
	}
}

?>
