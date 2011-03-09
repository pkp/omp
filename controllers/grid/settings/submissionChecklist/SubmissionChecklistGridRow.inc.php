<?php

/**
 * @file controllers/grid/settings/submissionChecklist/SubmissionChecklistGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionChecklistGridRow
 * @ingroup controllers_grid_settings_submissionChecklist
 *
 * @brief Handle submissionChecklist grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SubmissionChecklistGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SubmissionChecklistGridRow() {
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

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (isset($rowId) && is_numeric($rowId)) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new LegacyLinkAction(
					'editSubmissionChecklist',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editItem', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			$this->addAction(
				new LegacyLinkAction(
					'deleteSubmissionChecklist',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteItem', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				)
			);
		}
	}
}