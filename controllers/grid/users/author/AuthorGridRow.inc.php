<?php

/**
 * @file controllers/grid/users/author/AuthorGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridRow
 * @ingroup controllers_grid_users_author
 *
 * @brief Author grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class AuthorGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function AuthorGridRow() {
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

		// FIXME: #6199
		// Retrieve the monograph id from the request
		$monographId = $request->getUserVar('monographId');
		assert(is_numeric($monographId));

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monographId,
				'authorId' => $rowId
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editAuthor',
					new AjaxModal(
						$router->url($request, null, null, 'editAuthor', null, $actionArgs),
						__('grid.action.edit'),
						'edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteAuthor',
					new ConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteAuthor', null, $actionArgs)
					),
					__('grid.action.delete'),
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
