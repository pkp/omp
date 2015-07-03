<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for publication dates.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationDateGridCellProvider');
import('controllers.grid.catalogEntry.PublicationDateGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationDateGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var PublicationFormat */
	var $_publicationFormat;

	/**
	 * Constructor
	 */
	function PublicationDateGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER),
				array('fetchGrid', 'fetchRow', 'addDate', 'editDate',
				'updateDate', 'deleteDate'));
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
	 * Get the publication format assocated with these dates
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
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$representationId = null;

		// Retrieve the associated publication format for this grid.
		$publicationDateId = (int) $request->getUserVar('publicationDateId'); // set if editing or deleting a date

		if ($publicationDateId != '') {
			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
			$publicationDate = $publicationDateDao->getById($publicationDateId, $this->getMonograph()->getId());
			if ($publicationDate) {
				$representationId = $publicationDate->getPublicationFormatId();
			}
		} else { // empty form for new Date
			$representationId = (int) $request->getUserVar('representationId');
		}

		$monograph = $this->getMonograph();
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
		$this->setTitle('grid.catalogEntry.publicationDates');

		// Grid actions
		$router = $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addDate',
				new AjaxModal(
					$router->url($request, null, null, 'addDate', null, $actionArgs),
					__('grid.action.addDate'),
					'modal_add_item'
				),
				__('grid.action.addDate'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new PublicationDateGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'value',
				'grid.catalogEntry.dateValue',
				null,
				null,
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'code',
				'grid.catalogEntry.dateRole',
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
	 * @return PublicationDateGridRow
	 */
	function getRowInstance() {
		return new PublicationDateGridRow($this->getMonograph());
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
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$publicationFormat = $this->getPublicationFormat();
		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		$data = $publicationDateDao->getByPublicationFormatId($publicationFormat->getId());
		return $data->toArray();
	}


	//
	// Public Date Grid Actions
	//
	/**
	 * Edit a new (empty) date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function addDate($args, $request) {
		return $this->editDate($args, $request);
	}

	/**
	 * Edit a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editDate($args, $request) {
		// Identify the date to be updated
		$publicationDateId = (int) $request->getUserVar('publicationDateId');
		$monograph = $this->getMonograph();

		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate = $publicationDateDao->getById($publicationDateId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationDateForm');
		$publicationDateForm = new PublicationDateForm($monograph, $publicationDate);
		$publicationDateForm->initData();

		return new JSONMessage(true, $publicationDateForm->fetch($request));
	}

	/**
	 * Update a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateDate($args, $request) {
		// Identify the code to be updated
		$publicationDateId = $request->getUserVar('publicationDateId');
		$monograph = $this->getMonograph();

		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate = $publicationDateDao->getById($publicationDateId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationDateForm');
		$publicationDateForm = new PublicationDateForm($monograph, $publicationDate);
		$publicationDateForm->readInputData();
		if ($publicationDateForm->validate()) {
			$publicationDateId = $publicationDateForm->execute();

			if(!isset($publicationDate)) {
				// This is a new code
				$publicationDate = $publicationDateDao->getById($publicationDateId, $monograph->getId());
				// New added code action notification content.
				$notificationContent = __('notification.addedPublicationDate');
			} else {
				// code edit action notification content.
				$notificationContent = __('notification.editedPublicationDate');
			}

			// Create trivial notification.
			$currentUser = $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row = $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($publicationDateId);
			$row->setData($publicationDate);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			return new JSONMessage(true, $publicationDateForm->fetch($request));
		}
	}

	/**
	 * Delete a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteDate($args, $request) {

		// Identify the code to be deleted
		$publicationDateId = $request->getUserVar('publicationDateId');

		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate = $publicationDateDao->getById($publicationDateId, $this->getMonograph()->getId());
		if ($publicationDate != null) { // authorized

			$result = $publicationDateDao->deleteObject($publicationDate);

			if ($result) {
				$currentUser = $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationDate')));
				return DAO::getDataChangedEvent();
			} else {
				return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			}
		}
	}
}

?>
