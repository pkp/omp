<?php

/**
 * @file controllers/grid/plugins/PluginGridRow.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridRow
 * @ingroup controllers_grid_plugins
 *
 * @brief Plugin grid row definition
 */

import('lib.pkp.classes.controllers.grid.plugins.PKPPluginGridRow');

class PluginGridRow extends PKPPluginGridRow {
	/**
	 * Constructor
	 * @param $userRoles array
	 * @param $contextLevel int CONTEXT_...
	 */
	function PluginGridRow($userRoles, $contextLevel) {
		parent::PKPPluginGridRow($userRoles, $contextLevel);
	}


	//
	// Protected helper methods
	//
	/**
	 * Return if user can edit a plugin settings or not.
	 * @param $plugin Plugin
	 * @return boolean
	 */
	function _canEdit(&$plugin) {
		if ($plugin->isSitePlugin()) {
			if (in_array(ROLE_ID_SITE_ADMIN, $this->_userRoles)) {
				return true;
			}
		} elseif ($this->_contextLevel & CONTEXT_PRESS) {
			if (in_array(ROLE_ID_MANAGER, $this->_userRoles)) {
				return true;
			}
		}

		return false;
	}
}

?>
