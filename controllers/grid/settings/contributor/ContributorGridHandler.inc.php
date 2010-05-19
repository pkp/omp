<?php

/**
 * @file controllers/grid/settings/contributor/ContributorGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContributorGridHandler
 * @ingroup controllers_grid_contributor
 *
 * @brief Handle contributor grid requests.
 */

// Import grid base classes
import('controllers.grid.settings.SetupGridHandler');

// Import Contributor grid specific classes
import('controllers.grid.settings.contributor.ContributorGridRow');

class ContributorGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function ContributorGridHandler() {
		parent::SetupGridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addContributor', 'editContributor', 'updateContributor', 'deleteContributor'));
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
		$this->setTitle('grid.contributor.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$contributors = $context->getSetting('contributors');
		$contributors = isset($contributors) ? $contributors : array();
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

		// Columns
		$this->addColumn(new GridColumn('institution',
										'grid.columns.institution',
										null,
										'controllers/grid/gridCell.tpl'));
		$this->addColumn(new GridColumn('url',
										'grid.columns.url'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return ContributorGridRow
	 */
	function &getRowInstance() {
		$row = new ContributorGridRow();
		return $row;
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
		// Calling editContributor with an empty row id will add
		// a new contributor.
		return $this->editContributor($args, $request);
	}

	/**
	 * An action to edit a contributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editContributor(&$args, &$request) {
		$contributorId = isset($args['rowId']) ? $args['rowId'] : null;
		import('controllers.grid.settings.contributor.form.ContributorForm');
		$contributorForm = new ContributorForm($contributorId);

		if ($contributorForm->isLocaleResubmit()) {
			$contributorForm->readInputData();
		} else {
			$contributorForm->initData($args, $request);
		}

		$json = new JSON('true', $contributorForm->fetch());
		return $json->getString();
	}

	/**
	 * Update a contributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateContributor(&$args, &$request) {
		// -> contributorId must be present and valid
		// -> htmlId must be present and valid
		$contributorId = isset($args['rowId']) ? $args['rowId'] : null;
		import('controllers.grid.settings.contributor.form.ContributorForm');
		$contributorForm = new ContributorForm($contributorId);
		$contributorForm->readInputData();

		if ($contributorForm->validate()) {
			$contributorForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($contributorForm->contributorId);
			$rowData = array('institution' => $contributorForm->getData('institution'),
							'url' => $contributorForm->getData('url'));
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a contributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteContributor(&$args, &$request) {
		$contributorId = isset($args['rowId']) ? $args['rowId'] : null;
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		// get all of the contributors
		$contributors = $pressSettingsDao->getSetting($press->getId(), 'contributors');

		if ( isset($contributors[$contributorId]) ) {
			unset($contributors[$contributorId]);
			$pressSettingsDao->updateSetting($press->getId(), 'contributors', $contributors, 'object');
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingItem'));
		}
		return $json->getString();
	}
}