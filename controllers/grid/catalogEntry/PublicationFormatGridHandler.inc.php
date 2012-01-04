<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationFormatGridCellProvider');
import('controllers.grid.catalogEntry.PublicationFormatGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function PublicationFormatGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addFormat', 'editFormat',
				'updateFormat', 'deleteFormat'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this publication format grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
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

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('monograph.publicationFormats');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addFormat',
				new AjaxModal(
					$router->url($request, null, null, 'addFormat', null, $actionArgs),
					__('grid.action.addItem'),
					'addFormat'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new PublicationFormatGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'title',
				'grid.catalogEntry.publicationFormatTitle',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'format',
				'grid.catalogEntry.publicationFormatType',
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
	 * @return PublicationFormatGridRow
	 */
	function &getRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new PublicationFormatGridRow($monograph);
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$monograph =& $this->getMonograph();
		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$data =& $assignedPublicationFormatDao->getFormatsByMonographId($monograph->getId(), true);
		return $data;
	}


	//
	// Public Publication Format Grid Actions
	//

	function addFormat($args, $request) {
		return $this->editFormat($args, $request);
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFormat($args, &$request) {
		// Identify the format to be updated
		$assignedPublicationFormatId = (int) $request->getUserVar('assignedPublicationFormatId');
		$monograph =& $this->getMonograph();

		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat = $assignedPublicationFormatDao->getById($assignedPublicationFormatId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($monograph, $assignedPublicationFormat);
		$publicationFormatForm->initData();

		$json = new JSONMessage(true, $publicationFormatForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFormat($args, &$request) {
		// Identify the format to be updated
		$assignedPublicationFormatId = $request->getUserVar('assignedPublicationFormatId');
		$monograph =& $this->getMonograph();

		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$assignedPublicationFormat = $assignedPublicationFormatDao->getById($assignedPublicationFormatId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($monograph, $assignedPublicationFormat);
		$publicationFormatForm->readInputData();
		if ($publicationFormatForm->validate()) {
			$assignedPublicationFormatId = $publicationFormatForm->execute();

			if(!isset($assignedPublicationFormat)) {
				// This is a new format
				$assignedPublicationFormat =& $assignedPublicationFormatDao->getById($assignedPublicationFormatId);
				// New added format action notification content.
				$notificationContent = __('notification.addedPublicationFormat');
			} else {
				// Format edit action notification content.
				$notificationContent = __('notification.editedPublicationFormat');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($assignedPublicationFormatId);
			$row->setData($assignedPublicationFormat);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $publicationFormatForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteFormat($args, &$request) {

		// Identify the publiation format to be deleted
		$assignedPublicationFormatId = $request->getUserVar('assignedPublicationFormatId');

		$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
		$result = $assignedPublicationFormatDao->deleteAssignedPublicationFormatById($assignedPublicationFormatId);

		if ($result) {
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationFormat')));
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			return $json->getString();
		}

	}
}

?>
