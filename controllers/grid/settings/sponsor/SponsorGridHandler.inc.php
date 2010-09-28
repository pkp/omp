<?php

/**
 * @file controllers/grid/settings/sponsor/SponsorGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorGridHandler
 * @ingroup controllers_grid_settings_sponsor
 *
 * @brief Handle sponsor grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.sponsor.SponsorGridRow');

class SponsorGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function SponsorGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addSponsor', 'editSponsor', 'updateSponsor', 'deleteSponsor'));
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
		$this->setTitle('grid.sponsor.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$sponsors = $context->getSetting('sponsors');
		$sponsors = isset($sponsors) ? $sponsors : array();
		$this->setData($sponsors);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addSponsor',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addSponsor', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$this->addColumn(new GridColumn('institution',
										'grid.columns.institution',
										null,
										'controllers/grid/gridCell.tpl'));
		$this->addColumn(new GridColumn('url', 'grid.columns.url'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SponsorGridRow
	 */
	function &getRowInstance() {
		$row = new SponsorGridRow();
		return $row;
	}

	//
	// Public Sponsor Grid Actions
	//
	/**
	 * An action to add a new sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSponsor($args, &$request) {
		// Calling editSponsor with an empty row id will add
		// a new sponsor.
		return $this->editSponsor($args, $request);
	}


	/**
	 * An action to edit a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function editSponsor($args, &$request) {
		$sponsorId = isset($args['rowId'])?$args['rowId']:null;

		import('controllers.grid.settings.sponsor.form.SponsorForm');
		$sponsorForm = new SponsorForm($sponsorId);

		if ($sponsorForm->isLocaleResubmit()) {
			$sponsorForm->readInputData();
		} else {
			$sponsorForm->initData($args, $request);
		}

		$json = new JSON('true', $sponsorForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function updateSponsor($args, &$request) {
		// -> sponsorId must be present and valid
		// -> htmlId must be present and valid
		$sponsorId = isset($args['rowId'])?$args['rowId']:null;

		import('controllers.grid.settings.sponsor.form.SponsorForm');
		$sponsorForm = new SponsorForm($sponsorId);
		$sponsorForm->readInputData();

		if ($sponsorForm->validate()) {
			$sponsorForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($sponsorForm->sponsorId);
			$rowData = array('institution' => $sponsorForm->getData('institution'),
							'url' => $sponsorForm->getData('url'));
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Delete a sponsor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function deleteSponsor($args, &$request) {
		$sponsorId = isset($args['rowId'])?$args['rowId']:null;
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		// get all of the sponsors
		$sponsors = $pressSettingsDao->getSetting($press->getId(), 'sponsors');

		if (isset($sponsors[$sponsorId])) {
			unset($sponsors[$sponsorId]);
			$pressSettingsDao->updateSetting($press->getId(), 'sponsors', $sponsors, 'object');
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('settings.setup.errorDeletingItem'));
		}
		return $json->getString();
	}
}