<?php

/**
 * @file controllers/grid/settings/masthead/MastheadGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadGridRow
 * @ingroup controllers_grid_settings_masthead
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
	 * @param $request PKPRequest
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

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editMasthead',
					new AjaxModal(
						$router->url($request, null, null, 'editGroup', null, $actionArgs),
						__('grid.action.edit'),
						'edit',
						true),
					__('grid.action.edit'),
					'edit')
			);

			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteMasthead',
					new ConfirmationModal(
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'deleteGroup', null, $actionArgs)),
					__('grid.action.delete'),
					'delete')
			);
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
