<?php

/**
 * @file controllers/grid/BaseLocaleFileGridHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BaseLocaleFileGridHandler
 * @ingroup controllers_grid_locale
 *
 * @brief Base class for locale file based grids.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.translator.controllers.grid.LocaleFileGridRow');
import('lib.pkp.classes.controllers.grid.LiteralGridCellProvider');

abstract class BaseLocaleFileGridHandler extends GridHandler {
	/** @var TranslatorPlugin The translator plugin */
	static $plugin;

	/** @var string JQuery selector for containing tab element */
	var $tabsSelector;

	/** @var string Locale */
	var $locale;

	/**
	 * Set the translator plugin.
	 * @param $plugin StaticPagesPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Constructor
	 */
	function BaseLocaleFileGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array('index', 'fetchGrid', 'fetchRow', 'download', 'edit', 'save')
		);
	}


	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);

		$this->tabsSelector = $request->getUserVar('tabsSelector');
		$this->locale = $request->getUserVar('locale');
		if (!AppLocale::isLocaleValid($this->locale)) fatalError('Invalid locale.');

		// Columns
		$cellProvider = new LiteralGridCellProvider();
		$this->addColumn(new GridColumn(
			'name',
			'common.name',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
	}

	/**
	 * @copydoc Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new LocaleFileGridRow($this->tabsSelector, $this->locale);
	}

	/**
	 * @copydoc GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.PagingFeature');
		return array(new PagingFeature());
	}

	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array(
				'locale' => $this->locale,
				'tabsSelector' => $this->tabsSelector,
			)
		);
	}

	//
	// Public Grid Actions
	//
	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function download($args, $request) {
		$filename = $this->_getFilename($request);

		header('Content-Type: application/xml');
		header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
		header('Cache-Control: private');
		readfile($filename);
	}

	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	abstract function edit($args, $request);

	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	abstract function save($args, $request);

	/**
	 * Get the (validated) filename for the current request.
	 * @param $request PKPRequest
	 * @return string Filename
	 */
	abstract protected function _getFilename($request);
}

?>
