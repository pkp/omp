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
import('lib.pkp.classes.linkAction.request.ConfirmationModal');

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
				new LinkAction(
					'email',
					new AjaxModal(
						$router->url($request, null, null, 'editEmail', null, $actionArgs),
						__('grid.user.email'),
						'notify',
						true
						),
					__('grid.user.email'),
					'notify')
			);
			$this->addAction(
				new LinkAction(
					'edit',
					new AjaxModal(
						$router->url($request, null, null, 'editUser', null, $actionArgs),
						__('grid.user.edit'),
						'edit',
						true
						),
					__('grid.user.edit'),
					'edit')
			);
			if ($element->getDisabled()) {
				$actionArgs['enable'] = true;
				$this->addAction(
					new LinkAction(
						'enable',
						new AjaxModal(
							$router->url($request, null, null, 'editDisableUser', null, $actionArgs),
							__('grid.user.enable'),
							'enable',
							true
							),
						__('grid.user.enable'),
						'enable')
				);
			} else {
				$actionArgs['enable'] = false;
				$this->addAction(
					new LinkAction(
						'disable',
						new AjaxModal(
							$router->url($request, null, null, 'editDisableUser', null, $actionArgs),
							__('grid.user.disable'),
							'disable',
							true
							),
						__('grid.user.disable'),
						'disable')
				);
			}
			$this->addAction(
				new LinkAction(
					'remove',
					new ConfirmationModal(
						__('manager.people.confirmRemove'),
						null,
						$router->url($request, null, null, 'removeUser', null, $actionArgs)
						),
					__('grid.action.remove'),
					'delete')
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
