<?php

/**
 * @file controllers/grid/settings/plugins/SettingsPluginGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsPluginGridHandler
 * @ingroup controllers_grid_settings_plugins
 *
 * @brief Handle plugins grid requests.
 */

import('controllers.grid.plugins.PluginGridHandler');

class SettingsPluginGridHandler extends PluginGridHandler {
	/**
	 * Constructor
	 */
	function SettingsPluginGridHandler() {
		$roles = array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER);

		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER, array('editPressPluginSettings'));

		parent::PluginGridHandler($roles);
	}


	//
	// Extended methods from PluginGridHandler
	//
	/**
	* @see PluginGridHandler::loadData()
	*/
	function getCategoryData($categoryDataElement) {
		$plugins = parent::getCategoryData($categoryDataElement);

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();
		$singlePress = false;
		if ($presses->getCount() == 1) {
			$singlePress = true;
		}

		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		if ($singlePress) {
			if (in_array(ROLE_ID_SITE_ADMIN, $userRoles)) {
				$showSitePlugins = true;
			}
		} else {
			$showSitePlugins = false;
		}

		if ($showSitePlugins) {
			return $plugins;
		} else {
			$pressLevelPlugins = array();
			foreach ($plugins as $plugin) {
				if (!$plugin->isSitePlugin()) {
					$pressLevelPlugins[] = $plugin;
				}
				unset($plugin);
			}
			return $pressLevelPlugins;
		}
	}

	//
	// Overriden template methods.
	//
	/**
	* @see GridHandler::getRowInstance()
	*/
	function getRowInstance() {
		return parent::getRowInstance(CONTEXT_PRESS);
	}


	//
	// Public handler methods
	//
	/**
	* Show a modal with the plugin edit settings content.
	* (only for press level plugins).
	* @param $args array
	* @param $request Request
	* @return string
	*/
	function editPressPluginSettings ($args, &$request) {
		return $this->editPluginSettings($args, $request);
	}
}

?>
