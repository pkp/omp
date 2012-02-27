<?php

/**
 * @file controllers/grid/announcements/ManageAnnouncementGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageAnnouncementGridHandler
 * @ingroup controllers_grid_announcements
 *
 * @brief Handle announcements management grid requests.
 */

import('controllers.grid.announcements.AnnouncementGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');
import('controllers.grid.announcements.form.AnnouncementForm');

class ManageAnnouncementGridHandler extends AnnouncementGridHandler {
	/**
	 * Constructor
	 */
	function ManageAnnouncementGridHandler() {
		parent::AnnouncementGridHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('fetchGrid', 'fetchRow', 'moreInformation', 'addAnnouncement',
				'editAnnouncement', 'updateAnnouncement', 'deleteAnnouncement'));
	}


	//
	// Overridden template methods
	//
	/**
	 * @see GridHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see AnnouncementGridHandler::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		// Load language components
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Add grid action.
		$router =& $request->getRouter();

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addAnnouncement',
				new AjaxModal(
					$router->url($request, null, null, 'addAnnouncement', null, null),
					__('grid.action.addItem'),
					'add',
					true
				),
				__('grid.action.addItem'),
				'add')
		);
	}

	/**
	 * @see GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		import('controllers.grid.announcements.AnnouncementGridRow');
		return new AnnouncementGridRow();
	}


	//
	// Public handler methods.
	//
	/**
	 * Display form to add announcement.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function addAnnouncement($args, &$request) {
		return $this->editAnnouncement($args, $request);
	}

	/**
	* Display form to edit an announcement.
	* @param $args array
	* @param $request PKPRequest
	* @return string
	*/
	function editAnnouncement($args, &$request) {
		$announcementId = (int)$request->getUserVar('announcementId');
		$press =& $request->getPress();
		$pressId = $press->getId();

		if (checkPhpVersion('5.0.0')) {
			// WARNING: This form needs $this in constructor
			$announcementForm = new AnnouncementForm($pressId, $announcementId);
		} else {
			$announcementForm =& new AnnouncementForm($pressId, $announcementId);
		}

		$announcementForm->initData($args, $request);

		$json = new JSONMessage(true, $announcementForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save an edited/inserted announcement.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateAnnouncement($args, &$request) {

		// Identify the announcement Id.
		$announcementId = $request->getUserVar('announcementId');
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Form handling.
		if (checkPhpVersion('5.0.0')) {
			// WARNING: This form needs $this in constructor
			$announcementForm = new AnnouncementForm($pressId, $announcementId);
		} else {
			$announcementForm =& new AnnouncementForm($pressId, $announcementId);
		}

		$announcementForm->readInputData();

		if ($announcementForm->validate()) {
			$announcementForm->execute($request);

			if ($announcementId) {
				// Successful edit of an existing announcement.
				$notificationLocaleKey = 'notification.editedAnnouncement';
			} else {
				// Successful added a new announcement.
				$notificationLocaleKey = 'notification.addedAnnouncement';
			}

			// Record the notification to user.
			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __($notificationLocaleKey)));

			// Prepare the grid row data.
			return DAO::getDataChangedEvent($announcementId);
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	* Delete an announcement.
	* @param $args array
	* @param $request
	*/
	function deleteAnnouncement($args, $request) {
		$announcementId = (int) $request->getUserVar('announcementId');

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcementDao->deleteById($announcementId);

		// Create notification.
		$notificationManager = new NotificationManager();
		$user =& $request->getUser();
		$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedAnnouncement')));

		return DAO::getDataChangedEvent($announcementId);
	}
}

?>
