<?php

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 * If no press is selected, display the page to create a press.
	 * Otherwise, display the press dashboard.
	 *
	 * See _getTargetPress to check the logic to get a press
	 * for both cases (public and private).
	 *
	 * @param $args array
	 * @param $request Request
	 */
	function index($args, &$request) {
		$press = $this->_getTargetPress($request);
		$user =& $request->getUser();

		if ($user) {
			// Private access.
			if ($press) {
				$request->redirect($press->getPath(), 'dashboard');
			} else {
				if (Validation::isSiteAdmin()) {
					$request->redirect(null, 'admin', 'presses');
				} else {
					$request->redirect(null, 'user', 'index');
				}
			}
		} else {
			// Public access.
			$this->setupTemplate();
			$templateMgr =& TemplateManager::getManager($request);
			$templateMgr->assign('helpTopicId', 'user.home');

			if ($press) {
				$this->_displayPressIndexPage($press, &$templateMgr);
			} else {
				$site =& $request->getSite();
				$this->_displaySiteIndexPage($request, $site, &$templateMgr);
			}
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

	/**
	 * Returns a press, based in the request data.
	 * @param $request Request
	 * @return mixed Either a Press or null
	 */
	function _getTargetPress($request) {

		// Get the requested path.
		$router =& $request->getRouter();
		$requestedPath = $router->getRequestedContextPath(&$request);
		$press = null;

		if ($requestedPath == 'index') {
			// No press requested. Check how many presses has the site.
			$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
			$presses =& $pressDao->getPresses();
			$pressesCount = $presses->getCount();
			if ($pressesCount === 1) {
				// Return the unique press.
				$press =& $presses->next();
			} elseif ($pressesCount > 1) {
				// Decide wich press to return.
				$user =& $request->getUser();
				if ($user) {
					// We have a user (private access).
					$press =& $this->_getFirstUserPress($user, $presses->toArray());
				} else {
					// Get the site redirect.
					$press =& $this->_getSiteRedirectPress($request);
				}
			}
		} else {
			// Return the requested press.
			$press =& $router->getContext($request);
		}
		if (is_a($press, 'Press')) {
			return $press;
		}
		return null;
	}

	/**
	 * Return the first press that user is enrolled with.
	 * @param $user User
	 * @param $presses Array
	 * @return mixed Either Press or null
	 */
	function _getFirstUserPress($user, $presses) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press = null;
		foreach($presses as $workingPress) {
			$userIsEnrolled = $userGroupDao->userInAnyGroup($user->getId(), $workingPress->getId());
			if ($userIsEnrolled) {
				$press = $workingPress;
				break;
			}
		}
		return $press;
	}

	/**
	 * Return the press that is configured in site redirect setting.
	 * @param $request Request
	 * @return mixed Either Press or null
	 */
	function _getSiteRedirectPress($request) {
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$site =& $request->getSite();
		$press = null;
		if ($site) {
			if($site->getRedirect()) {
				$press = $pressDao->getPress($site->getRedirect());
			}
		}
		return $press;
	}
}

?>
