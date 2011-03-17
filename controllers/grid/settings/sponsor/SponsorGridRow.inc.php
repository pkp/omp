<?php

/**
 * @file controllers/grid/settings/sponsor/SponsorGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorGridRow
 * @ingroup controllers_grid_settings_sponsor
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
	 * @param $request PKPRequest
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
				new LegacyLinkAction(
					'editSponsor',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editSponsor', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			$this->addAction(
				new LegacyLinkAction(
					'deleteSponsor',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteSponsor', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				)
			);
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		}
	}
}

?>
