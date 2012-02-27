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
		$this->addColumn(
			new GridColumn('enabled',
				'common.enabled',
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
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
		$categoriesSymbolic = $this->loadData($request, null);
		$categories = array('all' => __('grid.plugin.allCategories'));
		foreach ($categoriesSymbolic as $category) {
			$categories[$category] = __("plugins.categories.$category");
		}
		$filterData = array('categories' => $categories);

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
			// Find all plugins that have the filter name string in their display names.
			$filteredPlugins = array();
			foreach ($plugins as $plugin) { /* @var $plugin Plugin */
				$pluginName = $plugin->getDisplayName();
				if (stristr($pluginName, $filter['pluginName']) !== false) {
					$filteredPlugins[$plugin->getName()] = $plugin;
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
		if (is_array($filter) && isset($filter['category']) && ($i = array_search($filter['category'], $categories)) !== false) {
			return array($filter['category']);
		} else {
			return $categories;
		}
	}


	//
	// Public handler methods.
	//
	/**
	 * Perform plugin-specific management functions.
	 * @param $args array
	 * @param $request object
	 */
	function plugin($args, &$request) {
		$verb = (string) $request->getUserVar('verb');

		$this->setupTemplate(true);

		$plugin =& $this->getAuthorizedContextObject(ASSOC_TYPE_PLUGIN); /* @var $plugin Plugin */
		$message = null;
		$pluginModalContent = null;
		if (!is_a($plugin, 'Plugin') || !$plugin->manage($verb, $args, $message, &$messageParams, &$pluginModalContent)) {
			if ($message) {
				$notificationManager = new NotificationManager();
				$user =& $request->getUser();
				$notificationManager->createTrivialNotification($user->getId(), $message, $messageParams);

				return DAO::getDataChangedEvent($plugin->getName());
			}
		}
		if ($pluginModalContent) {
			$json = new JSONMessage(true, $pluginModalContent);
			return $json->getString();
		}
	}
}

?>
