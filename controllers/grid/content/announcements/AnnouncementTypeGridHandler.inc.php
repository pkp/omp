<?php

/**
 * @file controllers/grid/content/announcements/AnnouncementTypeGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeGridHandler
 * @ingroup controllers_grid_content_announcements
 *
 * @brief Handle announcement type grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

import('controllers.grid.content.announcements.form.AnnouncementTypeForm');

class AnnouncementTypeGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function AnnouncementTypeGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'fetchGrid', 'fetchRow',
				'addAnnouncementType', 'editAnnouncementType',
				'updateAnnouncementType',
				'deleteAnnouncementType'
			)
		);
	}


	//
	// Overridden template methods
	//
	/**
	 * @see GridHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));

		$returner = parent::authorize($request, $args, $roleAssignments);
		$press =& $request->getPress();

		$announcementTypeId = $request->getUserVar('announcementTypeId');
		if ($announcementTypeId) {
			// Ensure announcement type is valid and for this context
			$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO'); /* @var $announcementTypeDao AnnouncementTypeDAO */
			$announcementType =& $announcementTypeDao->getById($announcementTypeId);
			if (!$announcementType || $announcementType->getAssocType() != ASSOC_TYPE_PRESS || $announcementType->getAssocId() != $press->getId()) {
				return false;
			}
		}

		return $returner;
	}

	/**
	 * @see GridHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setTitle('manager.announcementTypes');

		// Set the no items row text
		$this->setEmptyRowText('manager.announcementTypes.noneCreated');

		$press =& $request->getPress();

		// Columns
		import('controllers.grid.content.announcements.AnnouncementTypeGridCellProvider');
		$announcementTypeCellProvider = new AnnouncementTypeGridCellProvider();
		$this->addColumn(
			new GridColumn('name',
				'common.name',
				null,
				'controllers/grid/gridCell.tpl',
				$announcementTypeCellProvider,
				array('width' => 60)
			)
		);

		// Load language components
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Add grid action.
		$router =& $request->getRouter();

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addAnnouncementType',
				new AjaxModal(
					$router->url($request, null, null, 'addAnnouncementType', null, null),
					__('grid.action.addAnnouncementType'),
					'modal_add_item',
					true
				),
				__('grid.action.addAnnouncementType'),
				'add_item'
			)
		);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$press =& $request->getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$pressAnnouncementTypes =& $announcementTypeDao->getByAssoc(ASSOC_TYPE_PRESS, $press->getId());

		return $pressAnnouncementTypes;
	}

	/**
	 * @see GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		import('controllers.grid.content.announcements.AnnouncementTypeGridRow');
		return new AnnouncementTypeGridRow();
	}

	//
	// Public grid actions.
	//
	/**
	 * Display form to add announcement type.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function addAnnouncementType($args, &$request) {
		return $this->editAnnouncementType($args, $request);
	}

	/**
	 * Display form to edit an announcement type.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function editAnnouncementType($args, &$request) {
		$announcementTypeId = (int)$request->getUserVar('announcementTypeId');
		$press =& $request->getPress();
		$pressId = $press->getId();

		$announcementTypeForm = new AnnouncementTypeForm($pressId, $announcementTypeId);
		$announcementTypeForm->initData($args, $request);

		$json = new JSONMessage(true, $announcementTypeForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save an edited/inserted announcement type.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateAnnouncementType($args, &$request) {

		// Identify the announcement type id.
		$announcementTypeId = $request->getUserVar('announcementTypeId');
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Form handling.
		$announcementTypeForm = new AnnouncementTypeForm($pressId, $announcementTypeId);
		$announcementTypeForm->readInputData();

		if ($announcementTypeForm->validate()) {
			$announcementTypeForm->execute($request);

			if ($announcementTypeId) {
				// Successful edit of an existing announcement type.
				$notificationLocaleKey = 'notification.editedAnnouncementType';
			} else {
				// Successful added a new announcement type.
				$notificationLocaleKey = 'notification.addedAnnouncementType';
			}

			// Record the notification to user.
			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __($notificationLocaleKey)));

			// Prepare the grid row data.
			return DAO::getDataChangedEvent($announcementTypeId);
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	 * Delete an announcement type.
	 * @param $args array
	 * @param $request
	 */
	function deleteAnnouncementType($args, $request) {
		$announcementTypeId = (int) $request->getUserVar('announcementTypeId');

		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$announcementTypeDao->deleteById($announcementTypeId);

		// Create notification.
		$notificationManager = new NotificationManager();
		$user =& $request->getUser();
		$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedAnnouncementType')));

		return DAO::getDataChangedEvent($announcementTypeId);
	}
}

?>
