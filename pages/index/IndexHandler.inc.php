<?php

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	 * For public access:
	 * If no press is selected, display list of presses associated with this system.
	 * Otherwise, display the index page for the selected press.
	 *
	 * For private access (user is logged in):
	 * Display the dashboard.
	 *
	 * @param $args array
	 * @param $request Request
	 */
	function index($args, &$request) {
		$this->validate(); // FIXME: Replace with an authorization policy, see #6100.

		// Get the requested press.
		$router =& $request->getRouter();
		$requestedPressPath = $router->getRequestedContextPath(&$request);

		// No press requested: should we redirect to a specific press by default?
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$site =& $request->getSite();
		if ($requestedPressPath == 'index' && $site->getRedirect()) {
			$press =& $pressDao->getPress($site->getRedirect());
		} else {
			$press = & $router->getContext($request);
		}

		// Check whether the press exists and identify the actual target press path.
		if ($press && is_a($press, 'Press')) {
			$targetPressPath = $press->getPath();
		} else {
			$targetPressPath = 'index';
		}

		// Is this a private access? Then redirect to the dashboard of the target press.
		$user =& $request->getUser();
		if (is_a($user, 'User')) {
			$request->redirect($targetPressPath, 'dashboard');
			return;
		}

		// Do we have to redirect to another target press?
		if ($requestedPressPath != $targetPressPath && $targetPressPath != 'index') {
			$request->redirect($targetPressPath);
			return;
		}

		// This is a public request: set up the template.
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'user.home');

		// If the request is for the site, display the overview page with all presses.
		if ($targetPressPath == 'index') {
			$templateMgr->assign('intro', $site->getLocalizedIntro());
			$templateMgr->assign('pressFilesPath', Request::getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/presses/');
			$presses =& $pressDao->getEnabledPresses();
			$templateMgr->assign_by_ref('presses', $presses);
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
			$templateMgr->display('index/site.tpl');
		} else {
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
}

?>
