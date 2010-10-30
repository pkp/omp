<?php

/**
 * @file controllers/grid/users/user/UserEnrollmentGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserEnrollmentGridRow
 * @ingroup controllers_grid_users_user
 *
 * @brief User enrollment grid row definition
 */

import('controllers.grid.users.user.UserGridRow');

class UserEnrollmentGridRow extends UserGridRow {

	/**
	 * Constructor
	 */
	function UserEnrollmentGridRow() {
		parent::UserGridRow();
	}

	//
	// Overridden methods from GridRow
	//

	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId	
			);
			$this->addAction(
				new LinkAction(
					'email',
					LINK_ACTION_MODE_MODAL,
					null,
					$router->url($request, null, null, 'editEmail', null, $actionArgs),
					'grid.user.email',
					null,
					'notify'
				)
			);
			$this->addAction(
				new LinkAction(
					'edit',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editUser', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			// Unlike UserGridRow, we refresh the row for a removed user to
			// indicate the removal of user group assignments
			$this->addAction(
				new LinkAction(
					'remove',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'removeUser', null, $actionArgs),
					'grid.action.remove',
					null,
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}