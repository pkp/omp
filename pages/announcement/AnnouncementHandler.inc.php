<?php

/**
 * @file pages/announcement/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_announcement
 *
 * @brief Handle requests for public announcement functions.
 */


import('classes.handler.Handler');

class AnnouncementHandler extends Handler {
	/**
	 * Constructor
	 **/
	function AnnouncementHandler() {
		parent::Handler();
	}


	//
	// Implement methods from Handler.
	//
	function authorize($request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request));

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods.
	//
	/**
	 * Show public announcements page.
	 * @var $args array
	 * @var $request PKPRequest
	 * @return string
	 */
	function index($args, &$request) {
		$this->setupTemplate();

		$press =& $request->getPress();
		$announcementsIntro = $press->getLocalizedSetting('announcementsIntroduction');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('announcementsIntroduction', $announcementsIntro);

		$templateMgr->display('announcements/index.tpl');
	}
}

?>
