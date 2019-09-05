<?php

/**
 * @file controllers/grid/content/spotlights/ManageSpotlightsGridHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridHandler
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Handle grid requests for spotlights.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.content.spotlights.SpotlightsGridCellProvider');
import('controllers.grid.content.spotlights.SpotlightsGridRow');
import('controllers.grid.content.spotlights.form.SpotlightForm');

// import Spotlight class for class constants
import('classes.spotlight.Spotlight');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ManageSpotlightsGridHandler extends GridHandler {

	/**
	 * @var Press
	 */
	var $_press;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER),
				array('fetchGrid', 'fetchRow', 'addSpotlight', 'editSpotlight',
				'updateSpotlight', 'deleteSpotlight', 'itemAutocomplete'));
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the press associated with this grid.
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Set the Press (authorized)
	 * @param Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		$returner = parent::authorize($request, $args, $roleAssignments);

		$spotlightId = $request->getUserVar('spotlightId');
		if ($spotlightId) {
			$press = $request->getPress();
			$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
			$spotlight = $spotlightDao->getById($spotlightId);
			if ($spotlight == null || $spotlight->getPressId() != $press->getId()) {
				return false;
			}
		}

		return $returner;
	}

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Load locale components.
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_APP_MANAGER);

		// Basic grid configuration
		$this->setTitle('spotlight.spotlights');

		// Set the no items row text
		$this->setEmptyRowText('spotlight.noneExist');

		$press = $request->getPress();
		$this->setPress($press);

		// Columns
		import('controllers.grid.content.spotlights.SpotlightsGridCellProvider');
		$spotlightsGridCellProvider = new SpotlightsGridCellProvider();
		$this->addColumn(
			new GridColumn('title',
				'grid.content.spotlights.form.title',
				null,
				null,
				$spotlightsGridCellProvider,
				array('width' => 40)
			)
		);

		$this->addColumn(
			new GridColumn('itemTitle',
				'grid.content.spotlights.spotlightItemTitle',
				null,
				null,
				$spotlightsGridCellProvider,
				array('width' => 40)
			)
		);

		$this->addColumn(
			new GridColumn('type',
				'common.type',
				null,
				null,
				$spotlightsGridCellProvider
			)
		);

		// Add grid action.
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addSpotlight',
				new AjaxModal(
					$router->url($request, null, null, 'addSpotlight', null, null),
					__('grid.action.addSpotlight'),
					'modal_add_item'
				),
				__('grid.action.addSpotlight'),
				'add_item')
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SpotlightsGridRow
	 */
	function getRowInstance() {
		return new SpotlightsGridRow($this->getPress());
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter = null) {

		$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
		$press = $this->getPress();
		return $spotlightDao->getByPressId($press->getId());
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the press.
	 * @return array
	 */
	function getRequestArgs() {
		$press = $this->getPress();
		return array(
			'pressId' => $press->getId()
		);
	}

	//
	// Public Spotlights Grid Actions
	//

	function addSpotlight($args, $request) {
		return $this->editSpotlight($args, $request);
	}

	/**
	 * Edit a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editSpotlight($args, $request) {
		$spotlightId = (int)$request->getUserVar('spotlightId');
		$press = $request->getPress();
		$pressId = $press->getId();

		$spotlightForm = new SpotlightForm($pressId, $spotlightId);
		$spotlightForm->initData();

		return new JSONMessage(true, $spotlightForm->fetch($request));
	}

	/**
	 * Update a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateSpotlight($args, $request) {
		// Identify the spotlight entry to be updated
		$spotlightId = $request->getUserVar('spotlightId');

		$press = $this->getPress();

		$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
		$spotlight = $spotlightDao->getById($spotlightId, $press->getId());

		// Form handling
		$spotlightForm = new SpotlightForm($press->getId(), $spotlightId);

		$spotlightForm->readInputData();
		if ($spotlightForm->validate()) {
			$spotlightId = $spotlightForm->execute();

			if(!isset($spotlight)) {
				// This is a new entry
				$spotlight = $spotlightDao->getById($spotlightId, $press->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedSpotlight');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedSpotlight');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($spotlightId);
			$row->setData($spotlight);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			return new JSONMessage(true, $spotlightForm->fetch($request));
		}
	}

	/**
	 * Delete a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteSpotlight($args, $request) {

		// Identify the entry to be deleted
		$spotlightId = $request->getUserVar('spotlightId');

		$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
		$press = $this->getPress();
		$spotlight = $spotlightDao->getById($spotlightId, $press->getId());
		if ($spotlight != null) { // authorized

			$result = $spotlightDao->deleteObject($spotlight);

			if ($result) {
				$currentUser = $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedSpotlight')));
				return DAO::getDataChangedEvent();
			} else {
				return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			}
		}
	}

	/**
	 * Returns a JSON list for the autocomplete field. Fetches a list of possible spotlight options
	 * based on the spotlight type chosen.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function itemAutocomplete($args, $request) {
		$name = $request->getUserVar('name');
		$press = $this->getPress();
		$itemList = array();

		// get the items that match.
		$matches = array();

		$args = [
			'status' => STATUS_PUBLISHED,
			'contextId' => $press->getId(),
			'count' => 100
		];

		if ($name) {
			$args['searchPhrase'] = $name;
		}

		$submissions = Services::get('submission')->getMany($args);
		foreach ($submissions as $submission) {
			$matches[] = array('label' => $submission->getLocalizedTitle(), 'value' => $submission->getId() . ':' . SPOTLIGHT_TYPE_BOOK);
		}

		if (!empty($matches)) {
			$itemList[] = array('label' => PKPString::strtoupper(__('submission.monograph')), 'value' => '');
			$itemList = array_merge($itemList, $matches);
		}

		$matches = array();

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$allSeries = $seriesDao->getByPressId($press->getId());
		while ($series = $allSeries->next()) {
			if ($name == '' || preg_match('/'. preg_quote($name, '/') . '/i', $series->getLocalizedTitle())) {
				$matches[] = array('label' => $series->getLocalizedTitle(), 'value' => $series->getId() . ':' . SPOTLIGHT_TYPE_SERIES);
			}
		}

		if (!empty($matches)) {
			$itemList[] = array('label' => PKPString::strtoupper(__('manager.series.book')), 'value' => '');
			$itemList = array_merge($itemList, $matches);
		}

		if (count($itemList) == 0) {
			return $this->noAutocompleteResults();
		}

		return new JSONMessage(true, $itemList);
	}
}


