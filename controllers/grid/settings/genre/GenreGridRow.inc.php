<?php

/**
 * @file controllers/grid/settings/genre/GenreGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenreGridRow
 * @ingroup controllers_grid_settings_genre
 *
 * @brief Handle Genre grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class GenreGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function GenreGridRow() {
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
				'genreId' => $rowId
			);

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editGenre',
					new AjaxModal(
						$router->url($request, null, null, 'editGenre', null, $actionArgs),
						__('grid.action.edit'),
						null,
						true),
					__('grid.action.edit'),
					'edit')
			);

			import('lib.pkp.classes.linkAction.request.ConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteGenre',
					new ConfirmationModal(
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'deleteGenre', null, $actionArgs)),
					__('grid.action.delete'),
					'delete')
			);
		}
	}
}

?>
