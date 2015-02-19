<?php
/**
 * @file controllers/grid/announcements/form/AnnouncementForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	 * @param $pressId int
	 * @param $announcementId int leave as default for new announcement
	 * @param $readOnly boolean
	 */
	function AnnouncementForm($pressId, $announcementId = null, $readOnly = false) {
		parent::PKPAnnouncementForm($pressId, $announcementId);

		$this->_readOnly = $readOnly;

		// Validate date expire.
		$this->addCheck(new FormValidatorCustom($this, 'dateExpire', 'optional', 'manager.announcements.form.dateExpireValid', create_function('$dateExpire', '$today = getDate(); $todayTimestamp = mktime(0, 0, 0, $today[\'mon\'], $today[\'mday\'], $today[\'year\']); return (strtotime($dateExpire) > $todayTimestamp);')));

		// If provided, announcement type is valid
		$this->addCheck(new FormValidatorCustom($this, 'typeId', 'optional', 'manager.announcements.form.typeIdValid', create_function('$typeId, $pressId', '$announcementTypeDao = DAORegistry::getDAO(\'AnnouncementTypeDAO\'); if((int)$typeId === 0) { return true; } else { return $announcementTypeDao->announcementTypeExistsByTypeId($typeId, ASSOC_TYPE_PRESS, $pressId);}'), array($pressId)));
	}


	//
	// Getters and Setters
	//
	/**
	 * Return if this form is read only or not.
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}


	//
	// Extended methods from Form
	//
	/**
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('readOnly', $this->isReadOnly());
		$templateMgr->assign('selectedTypeId', $this->getData('typeId'));

		$announcementDao = DAORegistry::getDAO('AnnouncementDAO');
		$announcement = $announcementDao->getById($this->announcementId);
		$templateMgr->assign_by_ref('announcement', $announcement);

		$announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
		list($assocType, $assocId) = $this->_getAnnouncementTypesAssocId();
		$announcementTypeFactory = $announcementTypeDao->getByAssoc($assocType, $assocId);

		$announcementTypeOptions = array();
		if (!$announcementTypeFactory->wasEmpty()) {
			$announcementTypeOptions = array(0 => __('common.none'));
		}
		while ($announcementType = $announcementTypeFactory->next()) {
			$announcementTypeOptions[$announcementType->getId()] = $announcementType->getLocalizedTypeName();
		}
		$templateMgr->assign('announcementTypes', $announcementTypeOptions);

		return parent::fetch($request, 'controllers/grid/announcements/form/announcementForm.tpl');
	}

	//
	// Extended methods from PKPAnnouncementForm
	//
	/**
	 * @copydoc PKPAnnouncementForm::readInputData()
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('dateExpire'));
	}

	/**
	 * @see PKPAnnouncementForm::execute()
	 * @param $request PKPRequest
	 */
	function execute($request) {
		$announcement = parent::execute();
		$pressId = $this->getContextId();

		// Send a notification to associated users
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$notificationUsers = array();
		$allUsers = $userGroupDao->getUsersByContextId($pressId);
		while (!$allUsers->eof()) {
			$user = $allUsers->next();
			$notificationUsers[] = array('id' => $user->getId());
		}
		$notificationManager = new NotificationManager();
		if (!$this->announcementId) { // Only for new announcements
			foreach ($notificationUsers as $userRole) {
				$notificationManager->createNotification(
					$request, $userRole['id'], NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
					$pressId, ASSOC_TYPE_ANNOUNCEMENT, $announcement->getId()
				);
			}
			$notificationManager->sendToMailingList($request,
				$notificationManager->createNotification(
					$request, UNSUBSCRIBED_USER_NOTIFICATION, NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
					$pressId, ASSOC_TYPE_ANNOUNCEMENT, $announcement->getId()
				)
			);
		}

		return $announcement->getId();
	}


	//
	// Implement protected methods from PKPAnnouncementForm.
	//
	/**
	 * @see PKPAnnouncementForm::setDateExpire()
	 */
	function setDateExpire($announcement) {
		/* @var $announcement Announcement */
		$dateExpire = $this->getData('dateExpire');
		if ($dateExpire) {
			$announcement->setDateExpire(DAO::formatDateToDB($dateExpire, null, false));
		} else {
			// No date passed but null is acceptable for
			// announcements.
			$announcement->setDateExpire(null);
		}
		return true;
	}


	//
	// Private helper methdos.
	//
	/**
	 * @copydoc PKPAnnouncementForm::_getAnnouncementTypesAssocId()
	 */
	function _getAnnouncementTypesAssocId() {
		$pressId = $this->getContextId();
		return array(ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Helper function to assign the AssocType and the AssocId
	 * @param Announcement the announcement to be modified
	 */
	function _setAnnouncementAssocId($announcement) {
		$pressId = $this->getContextId();
		$announcement->setAssocType(ASSOC_TYPE_PRESS);
		$announcement->setAssocId($pressId);
	}
}

?>
