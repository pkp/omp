<?php

/**
 * @file controllers/grid/masthead/MastheadGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadGridHandler
 * @ingroup controllers_grid_masthead
 *
 * @brief Handle masthead grid requests.
 */

import('controllers.grid.GridMainHandler');

class MastheadGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 */
	function MastheadGridHandler() {
		parent::GridMainHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return MastheadRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.masthead.MastheadRowHandler');
			$rowHandler =& new MastheadRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addGroup'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic grid configuration
		$this->setId('masthead');
		$this->setTitle('grid.masthead.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$groupDAO =& DAORegistry::getDAO('GroupDAO');
		$groups = $groupDAO->getGroups(ASSOC_TYPE_PRESS, $context->getId());
		$this->setData($groups);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addMasthead',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addGroup', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	//
	// Public Masthead Grid Actions
	//
	/**
	 * An action to add a new masthead
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addGroup(&$args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.masthead.MastheadRowHandler');
		$mastheadRow =& new MastheadRowHandler();

		// Calling editMasthead with an empty row id will add
		// a new masthead.
		$mastheadRow->editGroup($args, $request);
	}
}