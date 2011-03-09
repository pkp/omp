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
			$this->addAction(
				new LegacyLinkAction(
					'editGenre',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editGenre', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			$this->addAction(
				new LegacyLinkAction(
					'deleteGenre',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteGenre', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					Locale::translate('common.confirmDelete')
				)
			);
		}
	}
}