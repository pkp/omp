<?php

/**
 * @file plugins/generic/addThis/AddThisStatisticsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddThisStatisticsGridHandler
 * @ingroup plugins_generic_addThis
 *
 * @brief Handle addThis plugin requests for statistics.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

class AddThisStatisticsGridHandler extends GridHandler {
	/** @var Plugin */
	var $_plugin;

	/**
	 * Constructor
	 */
	function AddThisStatisticsGridHandler($plugin) {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow'));

		$this->setPlugin($plugin);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the plugin associated with this grid.
	 * @return Plugin
	 */
	function &getPlugin() {
		return $this->_plugin;
	}

	/**
	 * Set the Plugin
	 * @param Plugin
	 */
	function setPlugin($plugin) {
		$this->_plugin =& $plugin;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		$plugin =& $this->getPlugin();
		$plugin->addLocaleData();

		// Basic grid configuration

		$this->setTitle('plugins.generic.addThis.grid.title');

		// Columns
		$plugin->import('AddThisStatisticsGridCellProvider');
		$cellProvider = new AddThisStatisticsGridCellProvider();
		$gridColumn = new GridColumn(
			'url',
			'common.url',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider,
			array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
		);

		$gridColumn->addFlag('html', true);

		$this->addColumn($gridColumn);

		$this->addColumn(
			new GridColumn(
				'shares',
				'plugins.generic.addThis.grid.shares',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return AddThisStatisticsGridRow
	 */
	function &getRowInstance() {
		$plugin =& $this->getPlugin();
		$plugin->import('AddThisStatisticsGridRow');
		$row = new AddThisStatisticsGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$plugin =& $this->getPlugin();
		$press =& $request->getPress();

		$addThisProfileId = $press->getSetting('addThisProfileId');
		$addThisUsername = $press->getSetting('addThisUsername');
		$addThisPassword = $press->getSetting('addThisPassword');

		$data = array();

		if (isset($addThisProfileId) && isset($addThisUsername) && isset($addThisPassword)) {
			$topSharedUrls = 'https://api.addthis.com/analytics/1.0/pub/shares/url.json?period=week&pubid='.urlencode($addThisProfileId).
				'&username='.urlencode($addThisUsername).
				'&password='.urlencode($addThisPassword);

			import('lib.pkp.classes.file.FileWrapper');
			$wrapper = FileWrapper::wrapper($topSharedUrls);
			$jsonData = $wrapper->contents();

			if ($jsonData != '') {
				import('lib.pkp.classes.core.JSONManager');
				$jsonManager = new JSONManager();
				$jsonMessage = $jsonManager->decode($jsonData);
				foreach ($jsonMessage as $statElement) {
					$data[] = array('url' => $statElement->url, 'shares' => $statElement->shares);
				}
			}
		}
		return $data;
	}
}
?>
