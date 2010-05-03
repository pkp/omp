<?php

/**
 * @file AnnouncementHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for announcement management functions. 
 */

// $Id$

import('lib.pkp.pages.manager.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::PKPAnnouncementHandler();
	}
	/**
	 * Display a list of announcements for the current press.
	 */
	function announcements() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::announcements();
	}

	/**
	 * Display a list of announcement types for the current press.
	 */
	function announcementTypes() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.announcements');
		parent::announcementTypes();
	}
	
	function &_getAnnouncements($rangeInfo = null) {
		$press =& Request::getPress();
		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcements =& $announcementDao->getAnnouncementsByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);

		return $announcements;
	}
	
	function &_getAnnouncementTypes($rangeInfo = null) {
		$press =& Request::getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		$announcements =& $announcementTypeDao->getAnnouncementTypesByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);

		return $announcements;
	}	

	/**
	 * Checks the announcement to see if it belongs to this press or scheduled press
	 * @param $announcementId int
	 * return bool
	 */	
	function _announcementIsValid($announcementId) {
		if ($announcementId == null) 
			return true;

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcement =& $announcementDao->getAnnouncement($announcementId);
		
		$press =& Request::getPress();
		if ( $announcement && $press 
			&& $announcement->getAssocType() == ASSOC_TYPE_PRESS 
			&& $announcement->getAssocId() == $press->getId())
				return true;
			
		return false;
	}	

	/**
	 * Checks the announcement type to see if it belongs to this press.  All announcement types are set at the press level.
	 * @param $typeId int
	 * return bool
	 */
	function _announcementTypeIsValid($typeId) {
		$press =& Request::getPress();
		$announcementTypeDao =& DAORegistry::getDAO('AnnouncementTypeDAO');
		return (($typeId != null && $announcementTypeDao->getAnnouncementTypeAssocId($typeId) == $press->getId()) || $typeId == null);
	}	
}

?>
