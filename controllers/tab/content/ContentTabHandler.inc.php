<?php

/**
 * @file controllers/tab/content/ContentTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContentTabHandler
 * @ingroup controllers_tab_content
 *
 * @brief Handle AJAX operations for tabs on content management page.
 */

// Import the base Handler.
import('controllers.tab.settings.ManagerSettingsTabHandler');

class ContentTabHandler extends ManagerSettingsTabHandler {

	/**
	 * Constructor
	 */
	function ContentTabHandler() {
		parent::ManagerSettingsTabHandler();
		$pageTabs = array(
			'announcements' => 'controllers/tab/content/announcements/announcements.tpl',
			'announcementTypes' => 'controllers.tab.content.announcements.form.AnnouncementTypeForm',
			'spotlights' => 'controllers/tab/content/spotlights/spotlights.tpl'
		);
		$this->setPageTabs($pageTabs);
	}
}

?>
