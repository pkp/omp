<?php

/**
 * @file controllers/tab/announcements/AnnouncementTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTabHandler
 * @ingroup controllers_tab_announcements
 *
 * @brief Handle AJAX operations for tabs on announcements management page.
 */

// Import the base Handler.
import('controllers.tab.settings.ManagerSettingsTabHandler');

class AnnouncementTabHandler extends ManagerSettingsTabHandler {

	/**
	 * Constructor
	 */
	function AnnouncementTabHandler() {
		parent::ManagerSettingsTabHandler();
		$pageTabs = array(
			'announcements' => 'controllers/tab/announcements/announcements.tpl',
			'announcementTypes' => 'controllers.tab.announcements.form.AnnouncementTypeForm'
		);
		$this->setPageTabs($pageTabs);
	}
}

?>
