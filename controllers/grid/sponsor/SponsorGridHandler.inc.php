<?php

/**
 * @file controllers/grid/sponsor/SponsorGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorGridHandler
 * @ingroup controllers_grid_sponsor
 *
 * @brief Handle sponsor grid requests.
 */

import('controllers.grid.GridMainHandler');

class SponsorGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 */
	function SponsorGridHandler() {
		parent::GridMainHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SponsorRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.sponsor.SponsorRowHandler');
			$rowHandler =& new SponsorRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addSponsor'));
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
		$this->setId('sponsor');
		$this->setTitle('grid.sponsor.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$sponsors = $context->getSetting('sponsors');
		$this->setData($sponsors);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addSponsor',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addSponsor', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	//
	// Public Sponsor Grid Actions
	//
	/**
	 * An action to add a new sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSponsor(&$args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.sponsor.SponsorRowHandler');
		$sponsorRow =& new SponsorRowHandler();

		// Calling editSponsor with an empty row id will add
		// a new sponsor.
		$sponsorRow->editSponsor($args, $request);
	}
}