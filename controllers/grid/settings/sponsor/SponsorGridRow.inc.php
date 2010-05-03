<?php

/**
 * @file controllers/grid/settings/sponsor/SponsorGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorGridRow
 * @ingroup controllers_grid_sponsor
 *
 * @brief Handle sponsor grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SponsorGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SponsorGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// add Grid Row Actions

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'editSponsor',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editSponsor', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				));
			$this->addAction(
				new GridAction(
					'deleteSponsor',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteSponsor', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete'
				));
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		}
	}
}