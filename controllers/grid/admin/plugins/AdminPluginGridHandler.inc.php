<?php

/**
 * @file controllers/grid/admin/plugins/AdminPluginGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPluginGridHandler
 * @ingroup controllers_grid_admin_plugins
 *
 * @brief Handle site level plugins grid requests.
 */

import('controllers.grid.plugins.PluginGridHandler');

class AdminPluginGridHandler extends PluginGridHandler {
	/**
	 * Constructor
	 */
	function AdminPluginGridHandler() {
		$roles = array(ROLE_ID_SITE_ADMIN);

		$this->addRoleAssignment($roles, array('editSitePluginSettings'));

		parent::PluginGridHandler($roles);
	}

	//
	// Overriden template methods.
	//
	/**
	* @see GridHandler::getRowInstance()
	*/
	function getRowInstance() {
		return parent::getRowInstance(CONTEXT_SITE);
	}


	//
	// Public handler methods
	//
	/**
	 * Show a modal with the plugin edit settings content.
	 * (only for site level plugins).
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function editSitePluginSettings ($args, &$request) {
		return $this->editPluginSettings($args, $request);
	}
}

?>
