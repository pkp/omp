<?php

/**
 * @file controllers/grid/submissions/submissionsList/SubmissionsListGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridRow
 * @ingroup controllers_grid_submissionsList
 *
 * @brief SubmissionsListGridRow grid row definition
 */

import('controllers.grid.GridRow');

class SubmissionsListGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SubmissionsListGridRow() {
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Set a non-default template that supports row actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		
		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $rowId,
			);

			$this->addAction(
				new GridAction(
					'deleteSubmission',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteSubmission', null, $actionArgs),
					'grid.action.delete',
					'delete'
				)
			);

		}
	}
}