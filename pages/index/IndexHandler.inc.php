<?php

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndexHandler
 * @ingroup pages_index
 *
 * @brief Handle site index requests.
 */


import('classes.handler.Handler');

class IndexHandler extends Handler {
	/**
	 * Constructor
	 */
	function IndexHandler() {
		parent::Handler();
	}


	//
	// Public handler operations
	//
	/**
	 * Display the site or press index page.
	 * (If a site admin is logged in and no presses exist, redirect to the
	 * press administration page -- this may be useful upon install.)
	 *
	 * @param $args array
	 * @param $request Request
	 */
	function index($args, &$request) {
		$press = $this->getTargetPress($request);
		$user =& $request->getUser();

		if ($user && !$press && Validation::isSiteAdmin()) {
			// If the user is a site admin and no press exists,
			// send them to press administration to create one.
			return $request->redirect(null, 'admin', 'presses');
		}

		// Public access.
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('helpTopicId', 'user.home');

		if ($press) {
			$this->_displayPressIndexPage($press, $templateMgr);
		} else {
			$site =& $request->getSite();
			$this->_displaySiteIndexPage($request, $site, $templateMgr);
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Display the site index page.
	 * @param $request PKPRequest
	 * @param $site Site
	 * @param $templateMgr TemplateManager
	 */
	function _displaySiteIndexPage($request, $site, &$templateMgr) {

		// Display the overview page with all presses.
		$templateMgr->assign('intro', $site->getLocalizedIntro());
		$templateMgr->assign('pressFilesPath', $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/presses/');
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$presses =& $pressDao->getEnabledPresses();
		$templateMgr->assign_by_ref('presses', $presses);
		$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		$templateMgr->display('index/site.tpl');

	}

	/**
	 * Display a given press index page.
	 * @param $press Press
	 * @param $templateMgr TemplateManager
	 */
	function _displayPressIndexPage($press, &$templateMgr) {

		// Assign header and content for home page.
		$templateMgr->assign('displayPageHeaderTitle', $press->getPressPageHeaderTitle(true));
		$templateMgr->assign('displayPageHeaderLogo', $press->getPressPageHeaderLogo(true));
		$templateMgr->assign('additionalHomeContent', $press->getLocalizedSetting('additionalHomeContent'));
		$templateMgr->assign('homepageImage', $press->getLocalizedSetting('homepageImage'));
		$templateMgr->assign('pressDescription', $press->getLocalizedSetting('description'));

		// Display creative commons logo/licence if enabled.
		$templateMgr->assign('displayCreativeCommons', $press->getSetting('includeCreativeCommons'));

		// Disable announcements if enabled.
		$enableAnnouncements = $press->getSetting('enableAnnouncements');
		$templateMgr->display('index/press.tpl');
	}
}

?>
