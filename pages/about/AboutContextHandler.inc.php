<?php

/**
 * @file pages/about/AboutContextHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutContextHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for context-level about functions.
 */

import('classes.handler.Handler');

class AboutContextHandler extends Handler {
	/**
	 * Constructor
	 */
	function AboutContextHandler() {
		parent::Handler();
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$context = $request->getContext();
		if (!$context || !$context->getSetting('restrictSiteAccess')) {
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}

		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display contact page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function contact($args, $request) {
		$pressSettingsDao = DAORegistry::getDAO('PressSettingsDAO');
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);
		$pressSettings = $pressSettingsDao->getSettings($press->getId());
		$templateMgr->assign('pressSettings', $pressSettings);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display description page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function description($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->display('about/description.tpl');
	}

	/**
	 * Display Press Sponsorship page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function pressSponsorship($args, $request) {
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('contributorNote', $press->getLocalizedSetting('contributorNote'));
		$templateMgr->assign('contributors', $press->getSetting('contributors'));
		$templateMgr->assign('sponsorNote', $press->getLocalizedSetting('sponsorNote'));
		$templateMgr->assign('sponsors', $press->getSetting('sponsors'));
		$templateMgr->display('about/pressSponsorship.tpl');
	}

	/**
	 * Display editorialTeam page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialTeam($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->display('about/editorialTeam.tpl');
	}

	/**
	 * Display editorialPolicies page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialPolicies($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->display('about/editorialPolicies.tpl');
	}

	/**
	 * Display submissions page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, $request) {
		$settingsDao = DAORegistry::getDAO('PressSettingsDAO');
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);
		$submissionChecklist = $press->getLocalizedSetting('submissionChecklist');
		if (!empty($submissionChecklist)) {
			ksort($submissionChecklist);
			reset($submissionChecklist);
		}
		$templateMgr->assign('submissionChecklist', $submissionChecklist);
		$templateMgr->display('about/submissions.tpl');
	}
}

?>
