<?php

/**
 * @file controllers/grid/submissionChecklist/SubmissionChecklistRowHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionChecklistRowHandler
 * @ingroup controllers_grid_submissionChecklist
 *
 * @brief Handle submissionChecklist grid row requests.
 */

import('controllers.grid.GridRowHandler');

class SubmissionChecklistRowHandler extends GridRowHandler {
	/**
	 * Constructor
	 */
	function SubmissionChecklistRowHandler() {
		parent::GridRowHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(),
				array('editItem', 'updateItem', 'deleteItem'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		$emptyActions = array();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('content', 'grid.submissionChecklist.column.checklistItem', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));

		parent::initialize($request);
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);

		// Actions
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'rowId' => $this->getId()
		);

		$this->addAction(
			new GridAction(
				'editSubmissionChecklist',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.submissionChecklist.SubmissionChecklistRowHandler', 'editItem', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteSubmissionChecklist',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.submissionChecklist.SubmissionChecklistRowHandler', 'deleteItem', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
	}

	//
	// Public SubmissionChecklist Row Actions
	//
	/**
	 * An action to edit a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editItem(&$args, &$request) {
		//FIXME: add validation here?
		$this->_configureRow($request, $args);

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
		$this->_configureRow($request, $args);

		import('controllers.grid.submissionChecklist.form.SubmissionChecklistForm');
		$submissionChecklistForm = new SubmissionChecklistForm($this->getId());
		$submissionChecklistForm->readInputData();

		if ($submissionChecklistForm->validate()) {
			$submissionChecklistForm->execute($args, $request);

			// prepare the grid row data
			$checklistItem = $submissionChecklistForm->getData('checklistItem');
			// use of 'content' as key is for backwards compatibility
			$rowData = array('content' => $checklistItem[Locale::getLocale()]);
			$this->setId($submissionChecklistForm->submissionChecklistId);
			$this->setData($rowData);

			$json = new JSON('true', $this->renderRowInternally($request));
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

		$this->_configureRow($request, $args);

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