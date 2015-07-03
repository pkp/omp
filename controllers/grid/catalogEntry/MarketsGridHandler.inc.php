<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for markets.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import format grid specific classes
import('controllers.grid.catalogEntry.MarketsGridCellProvider');
import('controllers.grid.catalogEntry.MarketsGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class MarketsGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var PublicationFormat */
	var $_publicationFormat;

	/**
	 * Constructor
	 */
	function MarketsGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER),
				array('fetchGrid', 'fetchRow', 'addMarket', 'editMarket',
				'updateMarket', 'deleteMarket'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this grid.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph = $monograph;
	}

	/**
	 * Get the publication format assocated with these markets
	 * @return PublicationFormat
	 */
	function getPublicationFormat() {
		return $this->_publicationFormat;
	}

	/**
	 * Set the publication format
	 * @param PublicationFormat
	 */
	function setPublicationFormat($publicationFormat) {
		$this->_publicationFormat = $publicationFormat;
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
		import('classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$this->setMonograph($monograph);
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$representationId = null;

		// Retrieve the associated publication format for this grid.
		$marketId = (int) $request->getUserVar('marketId'); // set if editing or deleting a market entry

		if ($marketId != '') {
			$marketDao = DAORegistry::getDAO('MarketDAO');
			$market = $marketDao->getById($marketId, $monograph->getId());
			if ($market) {
				$representationId = $market->getPublicationFormatId();
			}
		} else { // empty form for new Market
			$representationId = (int) $request->getUserVar('representationId');
		}

		$publicationFormat = $publicationFormatDao->getById($representationId, $monograph->getId());

		if ($publicationFormat) {
			$this->setPublicationFormat($publicationFormat);
		} else {
			fatalError('The publication format is not assigned to authorized monograph!');
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APP_DEFAULT,
			LOCALE_COMPONENT_PKP_DEFAULT
		);

		// Basic grid configuration
		$this->setTitle('grid.catalogEntry.markets');

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addMarket',
				new AjaxModal(
					$router->url($request, null, null, 'addMarket', null, $actionArgs),
					__('grid.action.addMarket'),
					'modal_add_item'
				),
				__('grid.action.addMarket'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new MarketsGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'territory',
				'grid.catalogEntry.marketTerritory',
				null,
				null,
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'rep',
				'grid.catalogEntry.representatives',
				null,
				null,
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'price',
				'monograph.publicationFormat.price',
				null,
				null,
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return MarketsGridRow
	 */
	function getRowInstance() {
		return new MarketsGridRow($this->getMonograph());
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph = $this->getMonograph();
		$publicationFormat = $this->getPublicationFormat();

		return array(
			'submissionId' => $monograph->getId(),
			'representationId' => $publicationFormat->getId()
		);
	}

	/**
	 * @copydoc GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$publicationFormat = $this->getPublicationFormat();
		$marketDao = DAORegistry::getDAO('MarketDAO');
		$data = $marketDao->getByPublicationFormatId($publicationFormat->getId());
		return $data->toArray();
	}


	//
	// Public  Market Grid Actions
	//
	/**
	 * Add a new market
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addMarket($args, $request) {
		return $this->editMarket($args, $request);
	}

	/**
	 * Edit a markets entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editMarket($args, $request) {
		// Identify the market entry to be updated
		$marketId = (int) $request->getUserVar('marketId');
		$monograph = $this->getMonograph();

		$marketDao = DAORegistry::getDAO('MarketDAO');
		$market = $marketDao->getById($marketId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.MarketForm');
		$marketForm = new MarketForm($monograph, $market);
		$marketForm->initData();

		return new JSONMessage(true, $marketForm->fetch($request));
	}

	/**
	 * Update a markets entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateMarket($args, $request) {
		// Identify the market entry to be updated
		$marketId = $request->getUserVar('marketId');
		$monograph = $this->getMonograph();

		$marketDao = DAORegistry::getDAO('MarketDAO');
		$market = $marketDao->getById($marketId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.MarketForm');
		$marketForm = new MarketForm($monograph, $market);
		$marketForm->readInputData();
		if ($marketForm->validate()) {
			$marketId = $marketForm->execute();

			if(!isset($market)) {
				// This is a new entry
				$market = $marketDao->getById($marketId, $monograph->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedMarket');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedMarket');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($marketId);
			$row->setData($market);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			return new JSONMessage(true, $marketForm->fetch($request));
		}
	}

	/**
	 * Delete a market entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteMarket($args, $request) {

		// Identify the markets entry to be deleted
		$marketId = $request->getUserVar('marketId');

		$marketDao = DAORegistry::getDAO('MarketDAO');
		$market = $marketDao->getById($marketId, $this->getMonograph()->getId());
		if ($market != null) { // authorized

			$result = $marketDao->deleteObject($market);

			if ($result) {
				$currentUser = $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedMarket')));
				return DAO::getDataChangedEvent();
			} else {
				return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			}
		}
	}
}

?>
