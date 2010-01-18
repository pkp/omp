<?php

/**
 * @file controllers/grid/contributor/ContributorGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContributorGridHandler
 * @ingroup controllers_grid_contributor
 *
 * @brief Handle contributor grid requests.
 */

import('controllers.grid.GridMainHandler');

class ContributorGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 */
	function ContributorGridHandler() {
		parent::GridMainHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return ContributorRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.contributor.ContributorRowHandler');
			$rowHandler =& new ContributorRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addContributor'));
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
		$this->setId('contributor');
		$this->setTitle('grid.contributor.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$contributors = $context->getSetting('contributors');
		$this->setData($contributors);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addContributor',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addContributor', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	//
	// Public Contributor Grid Actions
	//
	/**
	 * An action to add a new contributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addContributor(&$args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.contributor.ContributorRowHandler');
		$contributorRow =& new ContributorRowHandler();

		// Calling editContributor with an empty row id will add
		// a new contributor.
		$contributorRow->editContributor($args, $request);
	}
}