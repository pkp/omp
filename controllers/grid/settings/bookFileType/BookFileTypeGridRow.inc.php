<?php

/**
 * @file controllers/grid/settings/bookFileType/BookFileTypeGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileTypeGridRow
 * @ingroup controllers_grid_bookFileType
 *
 * @brief Handle Book File Type grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class BookFileTypeGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function BookFileTypeGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/**
	 * @see GridRow::initialize()
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'bookFileTypeId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'editBookFileType',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editBookFileType', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				));
			$this->addAction(
				new GridAction(
					'deleteBookFileType',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteBookFileType', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				));
		}
	}
}