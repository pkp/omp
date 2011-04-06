<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridRow
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief StageParticipant grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class StageParticipantGridRow extends GridRow {

	/**
	 * Constructor
	 */
	function StageParticipantGridRow() {
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

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row.
			$router =& $request->getRouter();
			$actionArgs = $this->getRequestArgs();
			$actionArgs['signoffId'] = $rowId;
			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			// FIXME: Not all roles should see this action. Bug #5975.
			$this->addAction(
				new LinkAction(
					'remove',
					new ConfirmationModal(
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteStageParticipant', null, $actionArgs)
					),
					__('grid.action.remove'),
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
