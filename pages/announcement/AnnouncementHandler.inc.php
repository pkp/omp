<?php

/**
 * @file pages/announcement/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_announcement
 *
 * @brief Handle requests for public announcement functions. 
 */

// $Id$


import('lib.pkp.pages.announcement.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::PKPAnnouncementHandler();
	}
	function _getAnnouncementsEnabled() {
		$press =& Request::getPress();
		return $press->getSetting('enableAnnouncements');
	}

	function &_getAnnouncements($rangeInfo = null) {
		$press =& Request::getPress();

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcements =& $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);
		$announcementsIntroduction = $press->getLocalizedSetting('announcementsIntroduction');

		return $announcements;
	}
	
	function _getAnnouncementsIntroduction() {
		$press =& Request::getPress();
		return $press->getLocalizedSetting('announcementsIntroduction');
	}
		
	function _announcementIsValid($announcementId) {
		$press =& Request::getPress();
		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');		
		return ($announcementId != null && $announcementDao->getAnnouncementAssocId($announcementId) == $press->getId());
	}
}

?>
