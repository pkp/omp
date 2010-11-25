<?php

/**
 * @file controllers/grid/files/galleyFiles/GalleyFilesGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GalleyFilesGridCategoryRow
 * @ingroup controllers_grid_users_chapter
 *
 * @brief GalleyFiles grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class GalleyFilesGridCategoryRow extends GridCategoryRow {
	/**
	 * Constructor
	 */
	function GalleyFilesGridCategoryRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph id from the request
		$monographId = $request->getUserVar('monographId');
		assert(is_numeric($monographId));

		// Is this a new row or an existing row?
		$fileId = $this->getId();
		if (!empty($fileId) && is_numeric($fileId)) {
			$monographFile =& $this->getData();

			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monographId,
				'fileId' => $fileId
			);

			$this->addAction(
				new LinkAction(
					'downloadFile',
					LINK_ACTION_MODE_LINK,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, null, 'downloadFile', null, $actionArgs),
					null,
					$monographFile->getLocalizedName()
				)
			);

		}
	}
}