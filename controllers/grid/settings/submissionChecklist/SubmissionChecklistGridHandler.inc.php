<?php

/**
 * @file controllers/grid/settings/submissionChecklist/SubmissionChecklistGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
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
				array('fetchGrid', 'addItem', 'editItem', 'updateItem', 'deleteItem'));
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
		$this->setGridDataElements($submissionChecklist[Locale::getLocale()]);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new LegacyLinkAction(
				'addItem',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addItem', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
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

		if ($submissionChecklistForm->isLocaleResubmit()) {
			$submissionChecklistForm->readInputData();
		} else {
			$submissionChecklistForm->initData($args, $request);
		}

		$json = new JSON(true, $submissionChecklistForm->fetch($request));
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

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$checklistItem = $submissionChecklistForm->getData('checklistItem');
			// use of 'content' as key is for backwards compatibility
			$rowData = array('content' => $checklistItem[Locale::getLocale()]);
			$row->setId($submissionChecklistForm->submissionChecklistId);
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON(true, $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON(false);
		}

		return $json->getString();
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

		foreach (Locale::getSupportedLocales() as $locale => $name) {
			if ( isset($submissionChecklistAll[$locale][$rowId]) ) {
				unset($submissionChecklistAll[$locale][$rowId]);
			} else {
				// only fail if the currently displayed locale was not set
				// (this is the one that needs to be removed from the currently displayed grid)
				if ( $locale == Locale::getLocale() ) {
					$json = new JSON(false, Locale::translate('manager.setup.errorDeletingSubmissionChecklist'));
					return $json->getString();
					exit;
				}
			}
		}

		$press->updateSetting('submissionChecklist', $submissionChecklistAll, 'object', true);
		$json = new JSON(true);
		return $json->getString();
	}
}

?>
