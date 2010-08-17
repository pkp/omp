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


import('lib.pkp.pages.announcement.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::PKPAnnouncementHandler();
		$this->addCheck(new HandlerValidatorPress($this));
	}

	/**
	 * @see PKPAnnouncementHandler::_getAnnouncementsEnabled()
	 */
	function _getAnnouncementsEnabled() {
		$press =& Request::getPress();
		return $press->getSetting('enableAnnouncements');
	}

	/**
	 * @see PKPAnnouncementHandler::_getAnnouncements()
	 */
	function &_getAnnouncements($rangeInfo = null) {
		$press =& Request::getPress();

		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		$announcements =& $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);

		return $announcements;
	}

	/**
	 * @see PKPAnnouncementHandler::_getAnnouncementsIntroduction()
	 */
	function _getAnnouncementsIntroduction() {
		$press =& Request::getPress();
		return $press->getLocalizedSetting('announcementsIntroduction');
	}

	/**
	 * @see PKPAnnouncementHandler::_announcementIsValid()
	 */
	function _announcementIsValid($announcementId) {
		$press =& Request::getPress();
		$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
		return ($announcementId != null && $announcementDao->getAnnouncementAssocId($announcementId) == $press->getId());
	}
}

?>
