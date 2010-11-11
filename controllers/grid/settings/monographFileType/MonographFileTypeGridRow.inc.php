<?php

/**
 * @file controllers/grid/settings/monographFileType/MonographFileTypeGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileTypeGridRow
 * @ingroup controllers_grid_settings_monographFileType
 *
 * @brief Handle Monograph File Type grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class MonographFileTypeGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function MonographFileTypeGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
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
				'monographFileTypeId' => $rowId
			);
			$this->addAction(
				new LinkAction(
					'editMonographFileType',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editMonographFileType', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				));
			$this->addAction(
				new LinkAction(
					'deleteMonographFileType',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteMonographFileType', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				));
		}
	}
}