<?php

/**
 * @file controllers/grid/artworkFile/ArtworkFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileGridHandler
 * @ingroup controllers_grid_artworkFile
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.GridHandler');
import('controllers.grid.artworkFile.ArtworkFileGridRow');

class ArtworkFileGridHandler extends GridHandler {

	/**
	 * Constructor
	 */
	function ArtworkFileGridHandler() {
		parent::GridHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addArtworkFile', 'editArtworkFile', 'uploadArtworkFile', 'deleteArtworkFile'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$press =& $router->getPress($request);

		// Basic grid configuration
		$this->setTitle('grid.artworkFile.title');

		// Elements to be displayed in the grid
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
		$artworkFiles =& $artworkFileDao->getByPressId($press->getId());
		$this->setData($artworkFiles);

		// Add grid-level actions
		$router =& $request->getRouter();
		$actionArgs = array('gridId' => $this->getId());
		$this->addAction(
			new GridAction(
				'addArtworkFile',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addArtworkFile', null, $actionArgs),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$emptyActions = array();
		// Basic grid row configuration

		$this->addColumn(new GridColumn('file', 'grid.libraryFiles.column.files', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', null));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return ArtworkFileGridRow
	 */
	function &getRowInstance() {
		$row = new ArtworkFileGridRow();
		return $row;
	}
}