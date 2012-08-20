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
		$targetPress = $this->getTargetPress($request);
		$press =& $request->getPress();
		$user =& $request->getUser();

		if ($user && !$targetPress && Validation::isSiteAdmin()) {
			// If the user is a site admin and no press exists,
			// send them to press administration to create one.
			return $request->redirect(null, 'admin', 'presses');
		}

		// Public access.
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('helpTopicId', 'user.home');

		if ($press) {
			// Display the current press home.
			$this->_displayPressIndexPage($press, $templateMgr);
		} elseif ($targetPress) {
			// We're not on a press homepage, but there's one
			// available; redirect there.
			$request->redirect($targetPress->getPath());
		} else {
			// A target press couldn't be determined for some reason.
			if ($user) {
				// Redirect to user profile.
				$request->redirect(null, 'user', 'profile');
			} else {
				// Not logged in. Redirect to login page.
				$request->redirect(null, 'login');
			}
		}
	}


	//
	// Private helper methods.
	//
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

		// Display announcements if enabled.
		$enableAnnouncements = $press->getSetting('enableAnnouncements');
		if ($enableAnnouncements) {
			$enableAnnouncementsHomepage = $press->getSetting('enableAnnouncementsHomepage');
			if ($enableAnnouncementsHomepage) {
				$numAnnouncementsHomepage = $press->getSetting('numAnnouncementsHomepage');
				$templateMgr->assign('enableAnnouncementsHomepage', true);
				$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
				$announcements =& $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_PRESS, $press->getId());
				$templateMgr->assign_by_ref('announcements', $announcements);
				if (isset($numAnnouncementsHomepage)) {
					$templateMgr->assign('numAnnouncementsHomepage', $numAnnouncementsHomepage);
				}
			}
		}

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs->toAssociativeArray());

		// Expose the featured monograph IDs and associated params
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $press->getId());
		$templateMgr->assign('featuredMonographIds', $featuredMonographIds);

		// Include any spotlight items for the press home page.
		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
		$spotlights =& $spotlightDao->getByPressId($press->getId());
		$templateMgr->assign_by_ref('spotlights', $spotlights);

		// Include any social media items that are configured for the press itself.
		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia =& $socialMediaDao->getEnabledForPressByPressId($press->getId());
		$blocks = array();
		while ($media =& $socialMedia->next()) {
			$media->replaceCodeVars();
			$blocks[] = $media->getCode();
		}

		$templateMgr->assign_by_ref('socialMediaBlocks', $blocks);

		$templateMgr->display('index/press.tpl');
	}
}

?>
