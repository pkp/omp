<?php
/**
 * @file controllers/grid/announcements/form/AnnouncementForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementForm
 * @ingroup controllers_grid_announcements_form
 *
 * @brief Form for to read/create/edit announcements.
 */


import('lib.pkp.classes.manager.form.PKPAnnouncementForm');

class AnnouncementForm extends PKPAnnouncementForm {

	/** @var $_readOnly boolean */
	var $_readOnly;

	/**
	 * Constructor
	 * @param announcementId int leave as default for new announcement
	 */
	function AnnouncementForm($announcementId = null, $readOnly = false) {
		parent::PKPAnnouncementForm($announcementId);
		$press =& Request::getPress();

		$this->_readOnly = $readOnly;

		// If provided, announcement type is valid
		$this->addCheck(new FormValidatorCustom($this, 'typeId', 'optional', 'manager.announcements.form.typeIdValid', create_function('$typeId, $pressId', '$announcementTypeDao =& DAORegistry::getDAO(\'AnnouncementTypeDAO\'); return $announcementTypeDao->announcementTypeExistsByTypeId($typeId, ASSOC_TYPE_PRESS, $pressId);'), array($press->getId())));
	}


	//
	// Getters and Setters
	//
	/**
	 * Return if this form is read only or not.
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}


	//
	// Extended methods from Form
	//
	/**
	* @see Form::fetch()
	*/
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('readOnly', $this->isReadOnly());
		$templateMgr->assign('selectedTypeId', $this->getData('typeId'));

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcement =& $announcementDao->getAnnouncement($this->announcementId);
		$templateMgr->assign_by_ref('announcement', $announcement);

		return parent::fetch($request, 'controllers/grid/announcements/form/announcementForm.tpl');
	}

	//
	// Extended methods from PKPAnnouncementForm
	//
	/**
	 * @see PKPAnnouncementForm::display()
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::display();
	}

	/**
	 * @see PKPAnnouncementForm::execute()
	 */
	function execute(&$request) {
		$announcement = parent::execute();
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Send a notification to associated users
		$userGroupDao =& DAORegistry::getDAO('RoleAssignmentDAO');
		$notificationUsers = array();
		$allUsers = $userGroupDao->getUsersByContextId($pressId);
		while (!$allUsers->eof()) {
			$user =& $allUsers->next();
			$notificationUsers[] = array('id' => $user->getId());
			unset($user);
		}
		$url = $request->url(null, 'announcement', 'view', array($announcement->getId()));
		$notificationManager = new NotificationManager();
		foreach ($notificationUsers as $userRole) {
			$notificationManager->createNotification(
				$userRole['id'], NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
				$pressId, ASSOC_TYPE_ANNOUNCEMENT, $announcement->getId()
			);
		}
		$notificationManager->sendToMailingList($request,
			$notificationManager->createNotification(
				$request, 0, NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
				$pressId, ASSOC_TYPE_ANNOUNCEMENT, $announcement->getId()
			)
		);
	}

	/**
	* @see PKPAnnouncementForm::_getAnnouncementTypesAssocId()
	*/
	function _getAnnouncementTypesAssocId() {
		$press =& Request::getPress();
		return array(ASSOC_TYPE_PRESS, $press->getId());
	}


	//
	// Private helper methdos.
	//
	/**
	 * Helper function to assign the AssocType and the AssocId
	 * @param Announcement the announcement to be modified
	 */
	function _setAnnouncementAssocId(&$announcement) {
		$press =& Request::getPress();
		$announcement->setAssocType(ASSOC_TYPE_PRESS);
		$announcement->setAssocId($press->getId());
	}
}

?>
