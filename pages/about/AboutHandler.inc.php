<?php

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for about functions.
 */

import('classes.handler.Handler');

class AboutHandler extends Handler {
	/**
	 * Constructor
	 */
	function AboutHandler() {
		parent::Handler();
	}

	/**
	 * Display about index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressPath = $request->getRequestedPressPath();

		if ($pressPath != 'index' && $pressDao->pressExistsByPath($pressPath)) {
			$press =& $request->getPress();

			$pressSettingsDao = DAORegistry::getDAO('PressSettingsDAO');
			$customAboutItems =& $pressSettingsDao->getSetting($press->getId(), 'customAboutItems');
			if (isset($customAboutItems[AppLocale::getLocale()])) $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getLocale()]);
			elseif (isset($customAboutItems[AppLocale::getPrimaryLocale()])) $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getPrimaryLocale()]);

			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, GROUP_CONTEXT_PEOPLE);

			$seriesDao =& DAORegistry::getDAO('SeriesDAO');
			$series =& $seriesDao->getByPressId($press->getId());

			$templateMgr->assign('seriesCount', $series->GetCount());
			$templateMgr->assign_by_ref('peopleGroups', $groups);
			$templateMgr->assign('helpTopicId', 'user.about');
			$templateMgr->display('about/index.tpl');
		} else {
			$site =& $request->getSite();
			$about = $site->getLocalizedAbout();
			$templateMgr->assign('about', $about);

			$presses =& $pressDao->getEnabledPresses(); //Enabled Added
			$templateMgr->assign_by_ref('presses', $presses);
			$templateMgr->display('about/site.tpl');
		}
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);

		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
	}

	/**
	 * Display contact page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function contact($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $pressSettingsDao->getPressSettings($press->getId());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display description page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function description($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $pressSettingsDao->getPressSettings($press->getId());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->display('about/description.tpl');
	}

	/**
	 * Display Press Sponsorship page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function pressSponsorship($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('contributorNote', $press->getLocalizedSetting('contributorNote'));
		$templateMgr->assign_by_ref('contributors', $press->getSetting('contributors'));
		$templateMgr->assign('sponsorNote', $press->getLocalizedSetting('sponsorNote'));
		$templateMgr->assign_by_ref('sponsors', $press->getSetting('sponsors'));
		$templateMgr->display('about/pressSponsorship.tpl');
	}

	/**
	 * Display editorialTeam page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialTeam($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request);

		$press =& $request->getPress();
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->display('about/editorialTeam.tpl');
	}

	/**
	 * Display editorialPolicies page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialPolicies($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$seriesList =& $seriesDao->getByPressId($press->getId());
		$seriesList =& $seriesList->toArray();
		$templateMgr->assign_by_ref('seriesList', $seriesList);

		$seriesEditorEntriesBySeries = array();
		foreach ($seriesList as $series) {
			$seriesEditorEntriesBySeries[$series->getId()] =& $seriesEditorsDao->getEditorsBySeriesId($series->getId(), $press->getId());
		}
		$templateMgr->assign_by_ref('seriesEditorEntriesBySeries', $seriesEditorEntriesBySeries);

		$templateMgr->display('about/editorialPolicies.tpl');
	}

	/**
	 * Display submissions page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request);

		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $settingsDao->getPressSettings($press->getId());
		$submissionChecklist = $press->getLocalizedSetting('submissionChecklist');
		if (!empty($submissionChecklist)) {
			ksort($submissionChecklist);
			reset($submissionChecklist);
		}
		$templateMgr->assign('submissionChecklist', $submissionChecklist);
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('helpTopicId','submission.authorGuidelines');
		$templateMgr->display('about/submissions.tpl');
	}

	/**
	 * Display aboutThisPublishingSystem page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function aboutThisPublishingSystem($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$versionDao =& DAORegistry::getDAO('VersionDAO');
		$version =& $versionDao->getCurrentVersion();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('ompVersion', $version->getVersionString());

		foreach (array(AppLocale::getLocale(), $primaryLocale = AppLocale::getPrimaryLocale(), 'en_US') as $locale) {
			$pubProcessFile = 'locale/'.$locale.'/pubprocesslarge.png';
			if (file_exists($pubProcessFile)) break;
		}
		$templateMgr->assign('pubProcessFile', $pubProcessFile);

		$templateMgr->display('about/aboutThisPublishingSystem.tpl');
	}
}

?>
