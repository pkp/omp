<?php

/**
 * @file controllers/grid/plugins/PluginGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridHandler
 * @ingroup controllers_grid_plugins
 *
 * @brief Handle plugins grid requests.
 */

import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class PluginGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function PluginGridHandler($roles) {
		if (is_null($roles)) {
			fatalError('Direct access not allowed!');
		}

		$this->addRoleAssignment($roles,
			array('fetchGrid, fetchRow'));

		parent::GridHandler();
	}


	//
	// Overridden template methods
	//
	/**
	 * @see GridHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {
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

	/**
	 * @see GridHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load language components
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_MANAGER);

		// Basic grid configuration
		$this->setTitle('common.plugins');

		// Set the no items row text
		$this->setEmptyRowText('grid.noItems');

		$press =& $request->getPress();

		// Columns
		import('controllers.grid.plugins.PluginGridCellProvider');
		$pluginCellProvider = new PluginGridCellProvider();
		$this->addColumn(
			new GridColumn('name',
				'common.name',
				null,
				'controllers/grid/gridCell.tpl',
				$pluginCellProvider
			)
		);

		$this->addColumn(
			new GridColumn('description',
				'common.description',
				null,
				'controllers/grid/gridCell.tpl',
				$pluginCellProvider
			)
		);
	}

	/**
	 * @see GridHandler::getFilterForm()
	 */
	function getFilterForm() {
		return 'controllers/grid/plugins/pluginGridFilter.tpl';
	}

	/**
	 * @see GridHandler::getFilterSelectionData()
	 */
	function getFilterSelectionData(&$request) {
		$category = $request->getUserVar('category');
		$pluginName = $request->getUserVar('pluginName');

		if (is_null($category)) {
			$category = 'all';
		}

		return array('category' => $category, 'pluginName' => $pluginName);
	}

	/**
	 * @see GridHandler::renderFilter()
	 */
	function renderFilter($request) {
		$filterData = array();
		$filterData['categories'] = $this->loadData($request, null);
		$filterData['categories']['all'] = __('grid.plugin.allCategories');

		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function getCategoryRowInstance() {
		import('controllers.grid.plugins.PluginCategoryGridRow');
		return new PluginCategoryGridRow();
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData($categoryDataElement, $filter) {
		$plugins =& PluginRegistry::loadCategory($categoryDataElement);

		if (!is_null($filter) && isset($filter['pluginName']) && $filter['pluginName'] != "") {
			// Find all plugins that have the filter name string in theirs display names.
			$filteredPlugins = array();
			foreach ($plugins as $plugin) {
				$pluginName = $plugin->getDisplayName();
				if (stristr($pluginName, $filter['pluginName']) !== false) {
					$filteredPlugins[$plugin->getPluginPath()] = $plugin;
				}
				unset($plugin);
			}
			return $filteredPlugins;
		}

		return $plugins;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @param $contextLevel int One of the CONTEXT_ constants.
	 */
	function getRowInstance($contextLevel) {
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		import('controllers.grid.plugins.PluginGridRow');
		return new PluginGridRow($userRoles, $contextLevel);
	}

	/**
	* @see GridHandler::loadData()
	*/
	function loadData($request, $filter) {
		$categories = PluginRegistry::getCategories();

		if (is_array($filter) && isset($filter['category']) && isset($categories[$filter['category']])) {
			return array($filter['category'] => $categories[$filter['category']]);
		} else {
			return $categories;
		}
	}


	//
	// Protected methods.
	//
	/**
	 * Show a modal with the plugin edit settings content.
	 * (both site and press level plugins).
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function editPluginSettings($args, &$request) {
		$category = $args['category'];
		$pluginName = $args['plugin'];
		$returner = $this->_delegateManagementVerb($category, $pluginName, 'settings');

		$json = new JSONMessage(true, $returner);
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Delegate to plugins their management functions
	 * and return the result.
	 * @param $category string
	 * @param $pluginName string
	 * @param $verb string
	 * @return string
	 */
	function _delegateManagementVerb($category, $pluginName, $verb) {
		$plugins =& PluginRegistry::loadCategory($category);
		$message = null;
		if (isset($plugins[$pluginName])) {
			return $plugins[$pluginName]->manage($verb, $args, $message);
		}
	}
}

?>
