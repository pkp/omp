<?php

/**
 * @file pages/manager/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for announcement management functions.
 */


import('lib.pkp.pages.manager.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::PKPAnnouncementHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'announcements', 'announcementTypes', 'createAnnouncement', 'createAnnouncementType',
				'deleteAnnouncement', 'deleteAnnouncementType', 'editAnnouncement', 'editAnnouncementType',
				'index', 'updateAnnouncement', 'updateAnnouncementType'
			)
		);
	}

	/**
	 * Display a list of announcements for the current press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function announcements($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::announcements($args, $request);
	}

	/**
	 * Display a list of announcement types for the current press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function announcementTypes($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::announcementTypes($args, $request);
	}

	/**
	 * @see PKPAnnouncementHandler::_getAnnouncements
	 */
	function &_getAnnouncements($request, $rangeInfo = null) {
		$press =& $request->getPress();
		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcements =& $announcementDao->getAnnouncementsByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);

		return $announcements;
	}

	/**
	 * @see PKPAnnouncementHandler::_getAnnouncementTypes
	 */
	function &_getAnnouncementTypes($request, $rangeInfo = null) {
		$press =& $request->getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$announcements =& $announcementTypeDao->getAnnouncementTypesByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);

		return $announcements;
	}

	/**
	 * Checks the announcement to see if it belongs to this press or scheduled press
	 * @param $request PKPRequest
	 * @param $announcementId int
	 * return bool
	 */
	function _announcementIsValid($request, $announcementId) {
		if ($announcementId == null) return true;

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcement =& $announcementDao->getAnnouncement($announcementId);

		$press =& $request->getPress();
		if ( $announcement && $press
			&& $announcement->getAssocType() == ASSOC_TYPE_PRESS
			&& $announcement->getAssocId() == $press->getId())
				return true;

		return false;
	}

	/**
	 * Checks the announcement type to see if it belongs to this press.  All announcement types are set at the press level.
	 * @param $request PKPRequest
	 * @param $typeId int
	 * return bool
	 */
	function _announcementTypeIsValid($request, $typeId) {
		$press =& $request->getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		return (($typeId != null && $announcementTypeDao->getAnnouncementTypeAssocId($typeId) == $press->getId()) || $typeId == null);
	}
}

?>
