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
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);

		$templateMgr =& TemplateManager::getManager($request);
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

		$templateMgr =& TemplateManager::getManager($request);
		$pressSettings =& $pressSettingsDao->getSettings($press->getId());
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

		$templateMgr =& TemplateManager::getManager($request);
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

		$templateMgr =& TemplateManager::getManager($request);
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
		$templateMgr =& TemplateManager::getManager($request);

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

		$templateMgr =& TemplateManager::getManager($request);
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

		$templateMgr =& TemplateManager::getManager($request);
		$submissionChecklist = $press->getLocalizedSetting('submissionChecklist');
		if (!empty($submissionChecklist)) {
			ksort($submissionChecklist);
			reset($submissionChecklist);
		}
		$templateMgr->assign('submissionChecklist', $submissionChecklist);
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

		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('ompVersion', $version->getVersionString(false));

		foreach (array(AppLocale::getLocale(), $primaryLocale = AppLocale::getPrimaryLocale(), 'en_US') as $locale) {
			$pubProcessFile = 'locale/'.$locale.'/pubprocesslarge.png';
			if (file_exists($pubProcessFile)) break;
		}
		$templateMgr->assign('pubProcessFile', $pubProcessFile);

		$templateMgr->display('about/aboutThisPublishingSystem.tpl');
	}
}

?>
