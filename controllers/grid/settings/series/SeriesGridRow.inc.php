<?php

/**
 * @file controllers/grid/settings/series/SeriesGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridRow
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SeriesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SeriesGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		$this->setupTemplate();

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new LegacyLinkAction(
					'editSeries',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editSeries', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			$this->addAction(
				new LegacyLinkAction(
					'deleteSeries',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteSeries', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				)
			);
		}

	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_APPLICATION_COMMON));
	}
}

?>
