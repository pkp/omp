<?php

/**
 * @file controllers/grid/settings/formats/PublicationFormatsGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsGridHandler
 * @ingroup controllers_grid_settings_formats
 *
 * @brief Handle publication formats on the production stage tab.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.formats.PublicationFormatsGridRow');
import('controllers.grid.settings.formats.PublicationFormatsGridCellProvider');
import('classes.publicationFormat.PublicationFormat');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatsGridHandler extends SetupGridHandler {

	/**
	 * Constructor
	 */
	function PublicationFormatsGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addFormat', 'editFormat',
				'updateFormat', 'deleteFormat'));
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

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_MANAGER
		);

		// Basic grid configuration
		$this->setTitle("manager.setup.publicationFormats");

		// Elements to be displayed in the grid
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormats =& $publicationFormatDao->getByPressId($context->getId());
		$this->setGridDataElements($publicationFormats);

		// Add grid-level actions
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
		$cellProvider = new PublicationFormatsGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'manager.setup.publicationFormat.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'code',
				'manager.setup.publicationFormat.code',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
				new GridColumn(
				'enabled',
				'manager.setup.publicationFormat.enabled',
				null,
				'controllers/grid/common/cell/checkMarkCell.tpl',
				$cellProvider
				)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return PublicationFormatsGridRow
	 */
	function &getRowInstance() {
		$row = new PublicationFormatsGridRow();
		return $row;
	}

	//
	// Public File Grid Actions
	//
	/**
	 * An action to add a new format
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addFormat($args, &$request) {
		return $this->editFormat($args, $request);
	}

	/**
	 * An action to edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFormat($args, &$request) {
		$router =& $request->getRouter();
		$context = $request->getContext();

		$formatId = (int) $request->getUserVar('formatId');

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($formatId, $context->getId());
		if (!isset($publicationFormat)) { // adding a new format
			$publicationFormat =& $publicationFormatDao->newDataObject();
			$publicationFormat->setPressId($context->getId());
			$publicationFormat->setEnabled(true); // enable new formats by default
		}
		// Form handling
		import('controllers.grid.settings.formats.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($context, $publicationFormat);
		$publicationFormatForm->initData();

		$json = new JSONMessage(true, $publicationFormatForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save changes to an existing format.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateFormat($args, &$request) {

		$router =& $request->getRouter();
		$context = $request->getContext();

		$formatId = $request->getUserVar('formatId');

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($formatId, $context->getId());

		// Form handling
		import('controllers.grid.settings.formats.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($context, $publicationFormat);
		$publicationFormatForm->readInputData();
		if ($publicationFormatForm->validate()) {
			$formatId = $publicationFormatForm->execute();

			if(!isset($publicationFormat)) {
				// This is a new format
				$publicationFormat =& $publicationFormatDao->getById($formatId, $context->getId());
				// New added format action notification content.
				$notificationContent = __('notification.addedPublicationFormat');
			} else {
				// format edited notification content.
				$notificationContent = __('notification.editedPublicationFormat');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($formatId);
			$row->setData($publicationFormat);
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
		$router =& $request->getRouter();
		$context = $request->getContext();

		// Identify the format to be deleted
		$formatId = $request->getUserVar('formatId');

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat =& $publicationFormatDao->getById($formatId, $context->getId());
		if (isset($publicationFormat)) { // authorized

			$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
			$count = $assignedPublicationFormatDao->getCountByPublicationFormatId($publicationFormat->getId());

			if ($count == 0) { // this format has not been assigned to a monograph yet, so allow the deletion.
				$result = $publicationFormatDao->deleteObject($publicationFormat);
				if ($result) {
					$currentUser =& $request->getUser();
					$notificationMgr = new NotificationManager();
					$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationFormat')));
					return DAO::getDataChangedEvent();
				} else {
					$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
					return $json->getString();
				}
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}

?>
