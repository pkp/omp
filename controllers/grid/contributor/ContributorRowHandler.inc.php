<?php

/**
 * @file controllers/grid/contributor/ContributorRowHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContributorRowHandler
 * @ingroup controllers_grid_contributor
 *
 * @brief Handle contributor grid row requests.
 */

import('controllers.grid.GridRowHandler');

class ContributorRowHandler extends GridRowHandler {
	/**
	 * Constructor
	 */
	function ContributorRowHandler() {
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
				array('editContributor', 'updateContributor', 'deleteContributor'));
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
		$this->addColumn(new GridColumn('institution', 'grid.columns.institution', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));
		$this->addColumn(new GridColumn('url', 'grid.columns.url'));		

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
				'editContributor',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.contributor.ContributorRowHandler', 'editContributor', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteContributor',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.contributor.ContributorRowHandler', 'deleteContributor', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));		
	}
	
	//
	// Public Contributor Row Actions
	//
	/**
	 * An action to edit a contributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editContributor(&$args, &$request) {
		//FIXME: add validation here?
		$this->_configureRow($request, $args);

		import('controllers.grid.contributor.form.ContributorForm');
		$contributorForm = new ContributorForm($this->getId());

		if ($contributorForm->isLocaleResubmit()) {
			$contributorForm->readInputData();
		} else {
			$contributorForm->initData($args, $request);
		}
		$contributorForm->display();
	}

	/**
	 * Update a contributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateContributor(&$args, &$request) {
		//FIXME: add validation here?
		// -> contributorId must be present and valid
		// -> htmlId must be present and valid
		$this->_configureRow($request, $args);

		import('controllers.grid.contributor.form.ContributorForm');
		$contributorForm = new ContributorForm($this->getId());
		$contributorForm->readInputData();

		if ($contributorForm->validate()) {
			$contributorForm->execute($args, $request);

			// prepare the grid row data			
			$rowData = array('institution' => $contributorForm->getData('institution'), 
							'url' => $contributorForm->getData('url'));
			$this->setId($contributorForm->contributorId);
			$this->setData($rowData);

			$json = new JSON('true', $this->renderRowInternally($request));
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
		// FIXME: add validation here?

		$this->_configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
 
		// get all of the contributors
		$contributors = $pressSettingsDao->getSetting($press->getId(), 'contributors');
 		$contributorId = $this->getId();

		if ( isset($contributors[$contributorId]) ) {
			unset($contributors[$contributorId]);
			$pressSettingsDao->updateSetting($press->getId(), 'contributors', $contributors, 'object');
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingContributor'));
		}
		echo $json->getString();
	}
}