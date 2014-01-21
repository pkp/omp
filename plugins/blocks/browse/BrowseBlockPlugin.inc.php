<?php

/**
 * @file plugins/blocks/browse/BrowseBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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
	 * Get the HTML contents of the browse block.
	 * @param $templateMgr PKPTemplateManager
	 * @return string
	 */
	function getContents($templateMgr, $request = null) {
		$press = $request->getPress();

		// Provide a list of series to browse
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getByPressId($press->getId());
		$templateMgr->assign('browseSeries', $series);

		// Provide a list of categories to browse
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$categories =& $categoryDao->getByPressId($press->getId());
		$templateMgr->assign('browseCategories', $categories);

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
