<?php

/**
 * @file controllers/grid/sponsor/SponsorRowHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorRowHandler
 * @ingroup controllers_grid_sponsor
 *
 * @brief Handle sponsor grid row requests.
 */

import('controllers.grid.GridRowHandler');

class SponsorRowHandler extends GridRowHandler {
	/**
	 * Constructor
	 */
	function SponsorRowHandler() {
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
				array('editSponsor', 'updateSponsor', 'deleteSponsor'));
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

	function configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::configureRow($request, $args);

		// Actions
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'rowId' => $this->getId()
		);
		$this->addAction(
			new GridAction(
				'editSponsor',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.sponsor.SponsorRowHandler', 'editSponsor', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteSponsor',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.sponsor.SponsorRowHandler', 'deleteSponsor', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
	}

	//
	// Public Sponsor Row Actions
	//
	/**
	 * An action to edit a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editSponsor(&$args, &$request) {
		//FIXME: add validation here?
		$this->configureRow($request, $args);

		import('controllers.grid.sponsor.form.SponsorForm');
		$sponsorForm = new SponsorForm($this->getId());

		if ($sponsorForm->isLocaleResubmit()) {
			$sponsorForm->readInputData();
		} else {
			$sponsorForm->initData($args, $request);
		}
		$sponsorForm->display();
	}

	/**
	 * Update a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateSponsor(&$args, &$request) {
		//FIXME: add validation here?
		// -> sponsorId must be present and valid
		// -> htmlId must be present and valid
		$this->configureRow($request, $args);

		import('controllers.grid.sponsor.form.SponsorForm');
		$sponsorForm = new SponsorForm($this->getId());
		$sponsorForm->readInputData();

		if ($sponsorForm->validate()) {
			$sponsorForm->execute($args, $request);

			// prepare the grid row data
			$rowData = array('institution' => $sponsorForm->getData('institution'),
							'url' => $sponsorForm->getData('url'));
			$this->setId($sponsorForm->sponsorId);
			$this->setData($rowData);

			$json = new JSON('true', $this->renderRowInternally($request));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSponsor(&$args, &$request) {
		// FIXME: add validation here?

		$this->configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		// get all of the sponsors
		$sponsors = $pressSettingsDao->getSetting($press->getId(), 'sponsors');
 		$sponsorId = $this->getId();

		if ( isset($sponsors[$sponsorId]) ) {
			unset($sponsors[$sponsorId]);
			$pressSettingsDao->updateSetting($press->getId(), 'sponsors', $sponsors, 'object');
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingItem'));
		}
		echo $json->getString();
	}
}