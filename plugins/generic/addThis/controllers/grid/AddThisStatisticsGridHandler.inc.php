<?php

/**
 * @file plugins/generic/addThis/controllers/grid/AddThisStatisticsGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2000-2017 John Willinsky
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
	static $_plugin;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('fetchGrid', 'fetchRow')
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the plugin associated with this grid.
	 * @return Plugin
	 */
	static function getPlugin() {
		return self::$_plugin;
	}

	/**
	 * Set the Plugin
	 * @param $plugin Plugin
	 */
	static function setPlugin($plugin) {
		self::$_plugin = $plugin;
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
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		parent::initialize($request);

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APP_DEFAULT,
			LOCALE_COMPONENT_PKP_DEFAULT
		);

		$plugin = $this->getPlugin();
		$plugin->addLocaleData();

		// Basic grid configuration

		$this->setTitle('plugins.generic.addThis.grid.title');

		// Columns
		$plugin->import('controllers.grid.AddThisStatisticsGridCellProvider');
		$cellProvider = new AddThisStatisticsGridCellProvider();
		$gridColumn = new GridColumn(
			'url',
			'common.url',
			null,
			null,
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
				null,
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
	function getRowInstance() {
		$plugin = $this->getPlugin();
		$plugin->import('AddThisStatisticsGridRow');
		return new AddThisStatisticsGridRow();
	}

	/**
	 * @copydoc GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$plugin = $this->getPlugin();
		$press = $request->getPress();

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
				$jsonMessage = json_decode($jsonData);
				foreach ($jsonMessage as $statElement) {
					$data[] = array('url' => $statElement->url, 'shares' => $statElement->shares);
				}
			}
		}
		return $data;
	}
}

?>
