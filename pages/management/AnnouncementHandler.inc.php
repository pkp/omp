<?php

/**
 * @file pages/management/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for announcement page.
 */

// Import the base ManagementHandler.
import('pages.management.ManagementHandler');

class AnnouncementHandler extends ManagementHandler {

	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('announcements')
		);
	}


	//
	// Public handler methods.
	//
	/**
	 * Display announcements page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function announcements($args, &$request) {
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('management/announcements/announcements.tpl');
	}
}

?>
