<?php

/**
 * @defgroup manager_form
 */

/**
 * @file classes/manager/form/AnnouncementForm.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementForm
 * @ingroup manager_form
 *
 * @brief Form for press managers to create/edit announcements.
 */

// $Id: AnnouncementForm.inc.php,v 1.2 2009/10/07 00:36:11 asmecher Exp $

import('manager.form.PKPAnnouncementForm');

class AnnouncementForm extends PKPAnnouncementForm {
	/**
	 * Constructor
	 * @param announcementId int leave as default for new announcement
	 */
	function AnnouncementForm($announcementId = null) {
		parent::PKPAnnouncementForm($announcementId);
		$press =& Request::getPress();

		// If provided, announcement type is valid
		$this->addCheck(new FormValidatorCustom($this, 'typeId', 'optional', 'manager.announcements.form.typeIdValid', create_function('$typeId, $pressId', '$announcementTypeDao =& DAORegistry::getDAO(\'AnnouncementTypeDAO\'); return $announcementTypeDao->announcementTypeExistsByTypeId($typeId, ASSOC_TYPE_PRESS, $pressId);'), array($press->getId())));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::display();
	}

	function _getAnnouncementTypesAssocId() {
		$press =& Request::getPress();
		return array(ASSOC_TYPE_PRESS, $press->getId());
	}

	/**
	 * Helper function to assign the AssocType and the AssocId
	 * @param Announcement the announcement to be modified
	 */
	function _setAnnouncementAssocId(&$announcement) {
		$press =& Request::getPress();
		$announcement->setAssocType(ASSOC_TYPE_PRESS);
		$announcement->setAssocId($press->getId());
	}

	/**
	 * Save announcement.
	 */
	function execute() {
		parent::execute();
		$press =& Request::getPress();
		$pressId = $press->getId();

		// Send a notification to associated users
		import('notification.Notification');
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$notificationUsers = array();
		$allUsers = $roleDao->getUsersByPressId($pressId);
		while (!$allUsers->eof()) {
			$user =& $allUsers->next();
			$notificationUsers[] = array('id' => $user->getId());
			unset($user);
		}
		$url = Request::url(null, 'announcement', 'view', array(1));
		foreach ($notificationUsers as $userRole) {
			Notification::createNotification($userRole['id'], "notification.type.newAnnouncement",
				null, $url, 1, NOTIFICATION_TYPE_NEW_ANNOUNCEMENT);
		}
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationDao->sendToMailingList(Notification::createNotification(0, "notification.type.newAnnouncement",
				null, $url, 1, NOTIFICATION_TYPE_NEW_ANNOUNCEMENT));
	}
}

?>
