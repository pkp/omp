<?php

/**
 * @file controllers/grid/submissionChecklist/SubmissionChecklistGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionChecklistGridHandler
 * @ingroup controllers_grid_submissionChecklist
 *
 * @brief Handle submissionChecklist grid requests.
 */

import('controllers.grid.GridMainHandler');

class SubmissionChecklistGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 */
	function SubmissionChecklistGridHandler() {
		parent::GridMainHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SubmissionChecklistRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.submissionChecklist.SubmissionChecklistRowHandler');
			$rowHandler =& new SubmissionChecklistRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addItem'));
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
		$this->setId('submissionChecklist');
		$this->setTitle('grid.submissionChecklist.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$submissionChecklist = $context->getSetting('submissionChecklist');
		$this->setData($submissionChecklist[Locale::getLocale()]);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addItem',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addItem', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	//
	// Public SubmissionChecklist Grid Actions
	//
	/**
	 * An action to add a new submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem(&$args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.submissionChecklist.SubmissionChecklistRowHandler');
		$submissionChecklistRow =& new SubmissionChecklistRowHandler();

		// Calling editSubmissionChecklist with an empty row id will add
		// a new submissionChecklist.
		$submissionChecklistRow->editItem($args, $request);
	}
}