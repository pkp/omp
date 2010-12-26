<?php

/**
 * @file controllers/grid/users/user/UserGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGridRow
 * @ingroup controllers_grid_users_user
 *
 * @brief User grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class UserGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function UserGridRow() {
		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$element =& $this->getData();
		assert(is_a($element, 'User'));

		$rowId = $this->getId();

		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new LegacyLinkAction(
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
				new LegacyLinkAction(
					'edit',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editUser', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			if ($element->getDisabled()) {
				$actionArgs['enable'] = true;
				$this->addAction(
					new LegacyLinkAction(
						'enable',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_REPLACE,
						$router->url($request, null, null, 'editDisableUser', null, $actionArgs),
						'grid.user.enable',
						null,
						'enable'
					)
				);
			} else {
				$actionArgs['enable'] = false;
				$this->addAction(
					new LegacyLinkAction(
						'disable',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_REPLACE,
						$router->url($request, null, null, 'editDisableUser', null, $actionArgs),
						'grid.user.disable',
						null,
						'disable'
					)
				);

			}
			$this->addAction(
				new LegacyLinkAction(
					'remove',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
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