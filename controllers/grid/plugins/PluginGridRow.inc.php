<?php

/**
 * @file controllers/grid/plugins/PluginGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridRow
 * @ingroup controllers_grid_plugins
 *
 * @brief Plugin category grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PluginGridRow extends GridRow {

	/** @var Array */
	var $_userRoles;

	/** @var int */
	var $_contextLevel;

	/**
	 * Constructor
	 */
	function PluginGridRow($userRoles, $contextLevel) {
		$this->_userRoles = $userRoles;
		$this->_contextLevel = $contextLevel;

		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$plugin =& $this->getData();
		assert(is_a($plugin, 'Plugin'));

		$rowId = $this->getId();

		if (!is_null($rowId)) {
			$router =& $request->getRouter();

			// Only add row actions if this is an existing row
			$managementVerbs = $plugin->getManagementVerbs();

			// If plugin has not management verbs, we receive
			// null. Check for it before foreach.
			if (!is_null($managementVerbs)) {
				foreach ($managementVerbs as $verb) {
					// Get link actions for each management verb.
					// This can be defined on each plugin class.
				}
			}

			if ($this->_canManage($plugin)) {
				// Add the management verbs link actions to the row.

			}
		}
	}


	//
	// Private helper methods
	//
	/**
	 * Return if a user can manage a plugin or not.
	 * @param $plugin Plugin
	 * @return boolean
	 */
	function _canManage(&$plugin) {
		if ($plugin->isSitePlugin()) {
			if (in_array(ROLE_ID_SITE_ADMIN, $this->_userRoles)) {
				return true;
			}
		} elseif ($this->_contextLevel & CONTEXT_PRESS) {
			if (in_array(ROLE_ID_PRESS_MANAGER, $this->_userRoles)) {
				return true;
			}
		}

		return false;
	}
}

?>
