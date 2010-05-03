<?php

/**
 * @file IndexHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndexHandler
 * @ingroup pages_index
 *
 * @brief Handle site index requests.
 */

// $Id$


import('classes.handler.Handler');

class IndexHandler extends Handler {
	/**
	 * Constructor
	 */
	function IndexHandler() {
		parent::Handler();
	}

	/**
	 * If no press is selected, display list of presses associated with this system.
	 * Otherwise, display the index page for the selected press.
	 */
	function index($args) {
		$this->validate();
		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressPath = Request::getRequestedPressPath();
		$templateMgr->assign('helpTopicId', 'user.home');

		if ($pressPath != 'index' && $pressDao->pressExistsByPath($pressPath)) {//if the request is for a M, display M page
			$press =& Request::getPress();

			// Assign header and content for home page
			$templateMgr->assign('displayPageHeaderTitle', $press->getPressPageHeaderTitle(true));
			$templateMgr->assign('displayPageHeaderLogo', $press->getPressPageHeaderLogo(true));
			$templateMgr->assign('additionalHomeContent', $press->getLocalizedSetting('additionalHomeContent'));
			$templateMgr->assign('homepageImage', $press->getLocalizedSetting('homepageImage'));
			$templateMgr->assign('pressDescription', $press->getLocalizedSetting('description'));

			// Display creative commons logo/licence if enabled
			$templateMgr->assign('displayCreativeCommons', $press->getSetting('includeCreativeCommons'));

			$enableAnnouncements = $press->getSetting('enableAnnouncements');
			$templateMgr->display('index/press.tpl');
		} else {
			$site =& Request::getSite();

			if ($site->getRedirect() && ($press = $pressDao->getPress($site->getRedirect())) != null) {
				Request::redirect($press->getPath());
			}

			$templateMgr->assign('intro', $site->getLocalizedIntro());
			$templateMgr->assign('pressFilesPath', Request::getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/presses/');
			$presses =& $pressDao->getEnabledPresses();
			$templateMgr->assign_by_ref('presses', $presses);
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
			$templateMgr->display('index/site.tpl');
		}
	}
}

?>
