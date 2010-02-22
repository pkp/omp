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

import('controllers.grid.GridHandler');
import('controllers.grid.submissionChecklist.SubmissionChecklistGridRow');

class SubmissionChecklistGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function SubmissionChecklistGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addItem', 'editItem', 'updateItem', 'deleteItem'));
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

		// Columns
		$emptyActions = array();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('content', 'grid.submissionChecklist.column.checklistItem', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SubmissionChecklistGridRow
	 */
	function &getRowInstance() {
		$row = new SubmissionChecklistGridRow();
		return $row;
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
		import('controllers.grid.submissionChecklist.SubmissionChecklistGridRow');
		$submissionChecklistRow =& new SubmissionChecklistGridRow();

		// Calling editSubmissionChecklist with an empty row id will add
		// a new submissionChecklist.
		$this->editItem($args, $request);
	}

	/**
	 * An action to edit a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editItem(&$args, &$request) {
		//FIXME: add validation here?

		import('controllers.grid.submissionChecklist.form.SubmissionChecklistForm');
		$submissionChecklistForm = new SubmissionChecklistForm($this->getId());

		if ($submissionChecklistForm->isLocaleResubmit()) {
			$submissionChecklistForm->readInputData();
		} else {
			$submissionChecklistForm->initData($args, $request);
		}
		$submissionChecklistForm->display();
	}

	/**
	 * Update a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateItem(&$args, &$request) {
		//FIXME: add validation here?
		// -> submissionChecklistId must be present and valid
		// -> htmlId must be present and valid

		import('controllers.grid.submissionChecklist.form.SubmissionChecklistForm');
		$submissionChecklistForm = new SubmissionChecklistForm($this->getId());
		$submissionChecklistForm->readInputData();

		if ($submissionChecklistForm->validate()) {
			$submissionChecklistForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$checklistItem = $submissionChecklistForm->getData('checklistItem');
			// use of 'content' as key is for backwards compatibility
			$rowData = array('content' => $checklistItem[Locale::getLocale()]);
			$row->setId($submissionChecklistForm->submissionChecklistId);
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteItem(&$args, &$request) {
		// FIXME: add validation here?

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		// get all of the submissionChecklists
		$submissionChecklistAll = $press->getSetting('submissionChecklist');
 		$submissionChecklistId = $this->getId();

		foreach (Locale::getSupportedLocales() as $locale => $name) {
			if ( isset($submissionChecklistAll[$locale][$submissionChecklistId]) ) {
				unset($submissionChecklistAll[$locale][$submissionChecklistId]);
			} else {
				// only fail if the currently displayed locale was not set
				// (this is the one that needs to be removed from the currently displayed grid)
				if ( $locale == Locale::getLocale() ) {
					$json = new JSON('false', Locale::translate('manager.setup.errorDeletingSubmissionChecklist'));
					echo $json->getString();
					exit;
				}
			}
		}

		$press->updateSetting('submissionChecklist', $submissionChecklistAll, 'object', true);
		$json = new JSON('true');
		echo $json->getString();
	}
}