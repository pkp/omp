<?php

/**
 * @file controllers/grid/admin/plugins/AdminPluginGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPluginGridHandler
 * @ingroup controllers_grid_admin_plugins
 *
 * @brief Handle site level plugins grid requests.
 */

import('lib.pkp.classes.controllers.grid.plugins.PluginGridHandler');

class AdminPluginGridHandler extends PluginGridHandler {
	/**
	 * Constructor
	 */
	function AdminPluginGridHandler() {
		$roles = array(ROLE_ID_SITE_ADMIN);

		$this->addRoleAssignment($roles, array('plugin'));

		parent::PluginGridHandler($roles);
	}

	//
	// Overriden template methods.
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		import('controllers.grid.plugins.PluginGridRow');
		return new PluginGridRow($userRoles, CONTEXT_PRESS);
	}

	/**
	 * @see GridHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		$category = $request->getUserVar('category');
		$pluginName = $request->getUserVar('plugin');
		$verb = $request->getUserVar('verb');

		if ($category && $pluginName) {
			import('classes.security.authorization.OmpPluginAccessPolicy');
			if ($verb) {
				$accessMode = ACCESS_MODE_MANAGE;
			} else {
				$accessMode = ACCESS_MODE_ADMIN;
			}

			$this->addPolicy(new OmpPluginAccessPolicy($request, $args, $roleAssignments, $accessMode));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>
