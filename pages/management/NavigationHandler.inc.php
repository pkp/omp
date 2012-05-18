<?php

/**
 * @file pages/management/NavigationHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for Navigation page.
 */

// Import the base ManagementHandler.
import('pages.management.ManagementHandler');

class NavigationHandler extends ManagementHandler {

	/**
	 * Constructor
	 **/
	function NavigationHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('navigation')
		);
	}


	//
	// Public handler methods.
	//
	/**
	 * Display Navigation tabs page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function navigation($args, &$request) {
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$templateMgr->assign('announcementsEnabled', $press->getSetting('enableAnnouncements')?true:false);
		$templateMgr->display('management/navigation/navigation.tpl');
	}
}

?>
