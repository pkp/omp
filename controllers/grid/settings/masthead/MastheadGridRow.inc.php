<?php

/**
 * @file controllers/grid/settings/masthead/MastheadGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadGridRow
 * @ingroup controllers_grid_masthead
 *
 * @brief Handle masthead grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class MastheadGridRow extends GridRow {
	/** @var group associated with the request **/
	var $group;

	/** @var group membership associated with the request **/
	var $groupMembership;

	/**
	 * Constructor
	 */
	function MastheadGridRow() {
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
		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'editMasthead',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editGroup', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				));
			$this->addAction(
				new GridAction(
					'deleteMasthead',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteGroup', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				));
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}

}