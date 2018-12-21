<?php

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	function __construct() {
		parent::__construct();
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
	function index($args, $request) {
		$targetPress = $this->getTargetContext($request);
		$press = $request->getPress();
		$user = $request->getUser();

		if ($user && !$targetPress && Validation::isSiteAdmin()) {
			// If the user is a site admin and no press exists,
			// send them to press administration to create one.
			return $request->redirect(null, 'admin', 'contexts');
		}

		// Public access.
		$this->setupTemplate($request);
		$templateMgr = TemplateManager::getManager($request);

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

		// Display New Releases
		if ($press->getSetting('displayNewReleases')) {
			$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
			$newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
			$templateMgr->assign('newReleases', $newReleases);
		}

		// Assign header and content for home page.
		$templateMgr->assign('additionalHomeContent', $press->getLocalizedSetting('additionalHomeContent'));
		$templateMgr->assign('homepageImage', $press->getLocalizedSetting('homepageImage'));
		$templateMgr->assign('pageTitleTranslated', $press->getLocalizedSetting('name'));

		// Display creative commons logo/licence if enabled.
		$templateMgr->assign('displayCreativeCommons', $press->getSetting('includeCreativeCommons'));

		// Display announcements if enabled.
		$enableAnnouncements = $press->getData('enableAnnouncements');
		$numAnnouncementsHomepage = $press->getData('numAnnouncementsHomepage');
		if ($enableAnnouncements && $numAnnouncementsHomepage) {
			$announcementDao = DAORegistry::getDAO('AnnouncementDAO');
			$announcements =& $announcementDao->getNumAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $press->getId(), $numAnnouncementsHomepage);
			$templateMgr->assign('announcements', $announcements->toArray());
			$templateMgr->assign('numAnnouncementsHomepage', $numAnnouncementsHomepage);
		}

		// Display Featured Books
		if ($press->getSetting('displayFeaturedBooks')) {
			$featureDao = DAORegistry::getDAO('FeatureDAO');
			$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $press->getId());
			$featuredMonographs = array();
			if (!empty($featuredMonographIds)) {
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonographs = $publishedMonographDao->getByPressId($press->getId());
				while ($publishedMonograph = $publishedMonographs->next()) {
					foreach($featuredMonographIds as $key => $val) {
						if ($publishedMonograph->getId() == $key) {
							$featuredMonographs[] = $publishedMonograph;
						}
					}
				}
			}
			$templateMgr->assign('featuredMonographs', $featuredMonographs);
		}

		// Display In Spotlight
		if ($press->getSetting('displayInSpotlight')) {
			// Include random spotlight items for the press home page.
			$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
			$spotlights = $spotlightDao->getRandomByPressId($press->getId(), MAX_SPOTLIGHTS_VISIBLE);
			$templateMgr->assign('spotlights', $spotlights);
		}

		$templateMgr->display('frontend/pages/index.tpl');
	}
}
