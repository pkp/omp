<?php

/**
 * @file controllers/grid/catalogEntry/SuppliersGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SuppliersGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for suppliers.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.SuppliersGridCellProvider');
import('controllers.grid.catalogEntry.SuppliersGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SuppliersGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function SuppliersGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addSupplier', 'editSupplier',
				'updateSupplier', 'deleteSupplier'));
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

		$supplierId = (int) $request->getUserVar('supplierId'); // set if editing or deleting a supplier entry

		if ($supplierId != '') {
			$supplierDao =& DAORegistry::getDAO('SupplierDAO');
			$supplier =& $supplierDao->getById($supplierId, $this->getMonograph()->getId());
			if (!isset($supplier)) {
				fatalError('Supplier referenced outside of authorized monograph context!');
			}
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('grid.catalogEntry.suppliers');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addSupplier',
				new AjaxModal(
					$router->url($request, null, null, 'addSupplier', null, $actionArgs),
					__('grid.action.addItem'),
					'addSupplier'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new SuppliersGridCellProvider();
		$this->addColumn(
				new GridColumn(
						'name',
						'grid.catalogEntry.supplierName',
						null,
						'controllers/grid/gridCell.tpl',
						$cellProvider
				)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'grid.catalogEntry.supplierRole',
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
	 * @return SuppliersGridRow
	 */
	function &getRowInstance() {
		$row = new SuppliersGridRow($this->getMonograph());
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		return array(
			'monographId' => $this->getMonograph()->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$supplierDao =& DAORegistry::getDAO('SupplierDAO');
		$data =& $supplierDao->getSuppliersByMonographId($this->getMonograph()->getId());
		return $data->toArray();
	}


	//
	// Public Suppliers Grid Actions
	//

	function addSupplier($args, $request) {
		return $this->editSupplier($args, $request);
	}

	/**
	 * Edit a supplier entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editSupplier($args, &$request) {
		// Identify the supplier entry to be updated
		$supplierId = (int) $request->getUserVar('supplierId');
		$monograph =& $this->getMonograph();

		$supplierDao =& DAORegistry::getDAO('SupplierDAO');
		$supplier = $supplierDao->getById($supplierId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.SupplierForm');
		$supplierForm = new SupplierForm($monograph, $supplier);
		$supplierForm->initData();

		$json = new JSONMessage(true, $supplierForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a supplier entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateSupplier($args, &$request) {
		// Identify the supplier entry to be updated
		$supplierId = $request->getUserVar('supplierId');
		$monograph =& $this->getMonograph();

		$supplierDao =& DAORegistry::getDAO('SupplierDAO');
		$supplier = $supplierDao->getById($supplierId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.SupplierForm');
		$supplierForm = new SupplierForm($monograph, $supplier);
		$supplierForm->readInputData();
		if ($supplierForm->validate()) {
			$supplierId = $supplierForm->execute();

			if(!isset($supplier)) {
				// This is a new entry
				$supplier =& $supplierDao->getById($supplierId, $monograph->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedSupplier');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedSupplier');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($supplierId);
			$row->setData($supplier);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $supplierForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a supplier entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSupplier($args, &$request) {

		// Identify the supplier entry to be deleted
		$supplierId = $request->getUserVar('supplierId');

		$supplierDao =& DAORegistry::getDAO('SupplierDAO');
		$supplier =& $supplierDao->getById($supplierId, $this->getMonograph()->getId());
		if ($supplier != null) { // authorized

			$result = $supplierDao->deleteObject($supplier);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedSupplier')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}

?>
