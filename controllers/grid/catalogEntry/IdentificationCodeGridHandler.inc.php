<?php

/**
 * @file controllers/grid/catalogEntry/IdentificationCodeGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for identification codes.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.IdentificationCodeGridCellProvider');
import('controllers.grid.catalogEntry.IdentificationCodeGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class IdentificationCodeGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var AssignedPublicationFormat */
	var $_assignedPublicationFormat;

	/**
	 * Constructor
	 */
	function IdentificationCodeGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addCode', 'editCode',
				'updateCode', 'deleteCode'));
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
	 * Get the assigned publication format assocated with these identification codes
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
		$identificationCodeId = (int) $request->getUserVar('identificationCodeId'); // set if editing or deleting a code

		if ($identificationCodeId != '') {
			$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
			$identificationCode =& $identificationCodeDao->getById($identificationCodeId);
			$assignedPublicationFormatId =& $identificationCode->getAssignedPublicationFormatId();
		} else { // empty form for new Code
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
		$this->setTitle('monograph.publicationFormat.productIdentifierType');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addCode',
				new AjaxModal(
					$router->url($request, null, null, 'addCode', null, $actionArgs),
					__('grid.action.addItem'),
					'addCode'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new IdentificationCodeGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'value',
				'grid.catalogEntry.identificationCodeValue',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'code',
				'grid.catalogEntry.identificationCodeType',
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
	 * @return IdentificationCodeGridRow
	 */
	function &getRowInstance() {
		$row = new IdentificationCodeGridRow($this->getMonograph());
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
		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$data =& $identificationCodeDao->getByAssignedPublicationFormatId($assignedPublicationFormat->getAssignedPublicationFormatId());
		return $data;
	}


	//
	// Public Identification Code Grid Actions
	//

	function addCode($args, $request) {
		return $this->editCode($args, $request);
	}

	/**
	 * Edit a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editCode($args, &$request) {
		// Identify the code to be updated
		$identificationCodeId = (int) $request->getUserVar('identificationCodeId');
		$monograph =& $this->getMonograph();

		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$identificationCode = $identificationCodeDao->getById($identificationCodeId);

		// Form handling
		import('controllers.grid.catalogEntry.form.IdentificationCodeForm');
		$identificationCodeForm = new IdentificationCodeForm($monograph, $identificationCode);
		$identificationCodeForm->initData();

		$json = new JSONMessage(true, $identificationCodeForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateCode($args, &$request) {
		// Identify the code to be updated
		$identificationCodeId = $request->getUserVar('identificationCodeId');
		$monograph =& $this->getMonograph();

		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$identificationCode = $identificationCodeDao->getById($identificationCodeId);

		// Form handling
		import('controllers.grid.catalogEntry.form.IdentificationCodeForm');
		$identificationCodeForm = new IdentificationCodeForm($monograph, $identificationCode);
		$identificationCodeForm->readInputData();
		if ($identificationCodeForm->validate()) {
			$identificationCodeId = $identificationCodeForm->execute();

			if(!isset($identificationCode)) {
				// This is a new code
				$identificationCode =& $identificationCodeDao->getById($identificationCodeId);
				// New added code action notification content.
				$notificationContent = __('notification.addedIdentificationCode');
			} else {
				// code edit action notification content.
				$notificationContent = __('notification.editedIdentificationCode');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($identificationCodeId);
			$row->setData($identificationCode);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $identificationCodeForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a code
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteCode($args, &$request) {

		// Identify the code to be deleted
		$identificationCodeId = $request->getUserVar('identificationCodeId');

		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$result = $identificationCodeDao->deleteById($identificationCodeId);

		if ($result) {
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedIdentificationCode')));
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			return $json->getString();
		}

	}
}

?>
