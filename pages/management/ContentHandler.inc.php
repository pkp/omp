<?php

/**
 * @file pages/management/ContentsHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContentsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for content page.
 */

// Import the base ManagementHandler.
import('pages.management.ManagementHandler');

class ContentHandler extends ManagementHandler {

	/**
	 * Constructor
	 **/
	function ContentHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('content')
		);
	}


	//
	// Public handler methods.
	//
	/**
	 * Display content tabs page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function content($args, &$request) {
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$templateMgr->assign('announcementsEnabled', $press->getSetting('enableAnnouncements')?true:false);
		$templateMgr->display('management/content/content.tpl');
	}
}

?>
