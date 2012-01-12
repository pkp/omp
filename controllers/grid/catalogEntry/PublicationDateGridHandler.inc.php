<?php

/**
 * @file controllers/grid/catalogEntry/PublicationDateGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
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

	/** @var AssignedPublicationFormat */
	var $_assignedPublicationFormat;

	/**
	 * Constructor
	 */
	function PublicationDateGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
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
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}

	/**
	 * Get the assigned publication format assocated with these dates
	 * @return AssignedPublicationformat
	 */
	function &getAssignedPublicationFormat() {
		return $this->_assignedPublicationFormat;
	}

	/**
	 * Set the assigned publication format
	 * @param AssignedPublicationFormat
	 */
	function setAssignedPublicationFormat($assignedPublicationFormat) {
		$this->_assignedPublicationFormat =& $assignedPublicationFormat;
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
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormatId = null;

		// Retrieve the associated publication format for this grid.
		$publicationDateId = (int) $request->getUserVar('publicationDateId'); // set if editing or deleting a date

		if ($publicationDateId != '') {
			$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
			$publicationDate =& $publicationDateDao->getById($publicationDateId, $this->getMonograph()->getId());
			if ($publicationDate) {
				$assignedPublicationFormatId =& $publicationDate->getAssignedPublicationFormatId();
			}
		} else { // empty form for new Date
			$assignedPublicationFormatId = (int) $request->getUserVar('assignedPublicationFormatId');
		}

		$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($assignedPublicationFormatId, $this->getMonograph()->getId());

		if ($assignedPublicationFormat) {
			$this->setAssignedPublicationFormat($assignedPublicationFormat);
		} else {
			fatalError('The publication format is not assigned to authorized monograph!');
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('grid.catalogEntry.publicationDates');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addDate',
				new AjaxModal(
					$router->url($request, null, null, 'addDate', null, $actionArgs),
					__('grid.action.addItem'),
					'addDate'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new PublicationDateGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'value',
				'grid.catalogEntry.publicationDateValue',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'code',
				'grid.catalogEntry.publicationDateRole',
				null,
				'controllers/grid/gridCell.tpl',
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
	function &getRowInstance() {
		$row = new PublicationDateGridRow($this->getMonograph());
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		$assignedPublicationFormat =& $this->getAssignedPublicationFormat();

		return array(
			'monographId' => $monograph->getId(),
			'assignedPublicationFormatId' => $assignedPublicationFormat->getAssignedPublicationFormatId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$assignedPublicationFormat =& $this->getAssignedPublicationFormat();
		$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
		$data =& $publicationDateDao->getByAssignedPublicationFormatId($assignedPublicationFormat->getAssignedPublicationFormatId());
		return $data->toArray();
	}


	//
	// Public Date Grid Actions
	//

	function addDate($args, $request) {
		return $this->editDate($args, $request);
	}

	/**
	 * Edit a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editDate($args, &$request) {
		// Identify the date to be updated
		$publicationDateId = (int) $request->getUserVar('publicationDateId');
		$monograph =& $this->getMonograph();

		$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate = $publicationDateDao->getById($publicationDateId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationDateForm');
		$publicationDateForm = new PublicationDateForm($monograph, $publicationDate);
		$publicationDateForm->initData();

		$json = new JSONMessage(true, $publicationDateForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateDate($args, &$request) {
		// Identify the code to be updated
		$publicationDateId = $request->getUserVar('publicationDateId');
		$monograph =& $this->getMonograph();

		$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate = $publicationDateDao->getById($publicationDateId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationDateForm');
		$publicationDateForm = new PublicationDateForm($monograph, $publicationDate);
		$publicationDateForm->readInputData();
		if ($publicationDateForm->validate()) {
			$publicationDateId = $publicationDateForm->execute();

			if(!isset($publicationDate)) {
				// This is a new code
				$publicationDate =& $publicationDateDao->getById($publicationDateId, $monograph->getId());
				// New added code action notification content.
				$notificationContent = __('notification.addedPublicationDate');
			} else {
				// code edit action notification content.
				$notificationContent = __('notification.editedPublicationDate');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($publicationDateId);
			$row->setData($publicationDate);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $publicationDateForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a date
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteDate($args, &$request) {

		// Identify the code to be deleted
		$publicationDateId = $request->getUserVar('publicationDateId');

		$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
		$publicationDate =& $publicationDateDao->getById($publicationDateId, $this->getMonograph()->getId());
		if ($publicationDate != null) { // authorized

			$result = $publicationDateDao->deleteObject($publicationDate);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationDate')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}

?>
