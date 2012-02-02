<?php

/**
 * @file controllers/grid/settings/submissionChecklist/SubmissionChecklistGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionChecklistGridHandler
 * @ingroup controllers_grid_settings_submissionChecklist
 *
 * @brief Handle submissionChecklist grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.submissionChecklist.SubmissionChecklistGridRow');

class SubmissionChecklistGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function SubmissionChecklistGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addItem', 'editItem', 'updateItem', 'deleteItem'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param $request PKPRequest
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
		$this->setGridDataElements($submissionChecklist[AppLocale::getLocale()]);

		// Add grid-level actions
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addItem',
				new AjaxModal(
					$router->url($request, null, null, 'addItem', null, array('gridId' => $this->getId())),
					__('grid.action.addItem'),
					null,
					true),
				__('grid.action.addItem'),
				'add_item')
		);

		// Columns
		$this->addColumn(
			new GridColumn(
				'content',
				'grid.submissionChecklist.column.checklistItem',
				null,
				'controllers/grid/gridCell.tpl',
				null,
				array('multiline' => true)
			)
		);
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
	function addItem($args, &$request) {
		// Delegate to the row handler
		import('controllers.grid.settings.submissionChecklist.SubmissionChecklistGridRow');
		$submissionChecklistRow = new SubmissionChecklistGridRow();

		// Calling editSubmissionChecklist with an empty row id will add
		// a new submissionChecklist.
		return $this->editItem($args, $request);
	}

	/**
	 * An action to edit a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editItem($args, &$request) {
		import('controllers.grid.settings.submissionChecklist.form.SubmissionChecklistForm');
		$submissionChecklistId = isset($args['rowId']) ? $args['rowId'] : null;
		$submissionChecklistForm = new SubmissionChecklistForm($submissionChecklistId);

		$submissionChecklistForm->initData($args, $request);

		$json = new JSONMessage(true, $submissionChecklistForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateItem($args, &$request) {
		// -> submissionChecklistId must be present and valid
		// -> htmlId must be present and valid

		import('controllers.grid.settings.submissionChecklist.form.SubmissionChecklistForm');
		$submissionChecklistId = isset($args['rowId']) ? $args['rowId'] : null;
		$submissionChecklistForm = new SubmissionChecklistForm($submissionChecklistId);
		$submissionChecklistForm->readInputData();

		if ($submissionChecklistForm->validate()) {
			$submissionChecklistForm->execute($args, $request);
			return DAO::getDataChangedEvent($submissionChecklistForm->submissionChecklistId);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Delete a submissionChecklist
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteItem($args, &$request) {
		$rowId = $request->getUserVar('rowId');

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		// get all of the submissionChecklists
		$submissionChecklistAll = $press->getSetting('submissionChecklist');

		foreach (AppLocale::getSupportedLocales() as $locale => $name) {
			if ( isset($submissionChecklistAll[$locale][$rowId]) ) {
				unset($submissionChecklistAll[$locale][$rowId]);
			} else {
				// only fail if the currently displayed locale was not set
				// (this is the one that needs to be removed from the currently displayed grid)
				if ( $locale == AppLocale::getLocale() ) {
					$json = new JSONMessage(false, __('manager.setup.errorDeletingSubmissionChecklist'));
					return $json->getString();
					exit;
				}
			}
		}

		$press->updateSetting('submissionChecklist', $submissionChecklistAll, 'object', true);
		return DAO::getDataChangedEvent($rowId);
	}
}

?>
