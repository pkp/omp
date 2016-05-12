<?php

/**
 * @file pages/about/AboutContextHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
	function authorize($request, &$args, $roleAssignments) {
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
	 * Display about page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$context = $request->getContext();
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->assign('aboutPress', $context->getLocalizedSetting('aboutPress'));
		$templateMgr->display('frontend/pages/about.tpl');
	}

	/**
	 * Display editorialTeam page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialTeam($args, $request) {
		$context = $request->getContext();
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->assign('editorialTeam', $context->getLocalizedSetting('masthead'));
		$templateMgr->display('frontend/pages/editorialTeam.tpl');
	}

	/**
	 * Display submissions page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, $request) {
		$context = $request->getContext();
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->assign('submissionInfo', AboutContextHandler::getSubmissionsInfo($context));
		$templateMgr->display('frontend/pages/submissions.tpl');
	}

	/**
	 * Display contact page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function contact($args, $request) {
		$context = $request->getContext();
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->assign('contact', AboutContextHandler::getContactInfo($context));
		$templateMgr->display('frontend/pages/contact.tpl');
	}


	//
	// Static protected methods.
	//
	/**
	* Get contact information used by contact operation.
	* @param $context Press
	* @return Array
	*/
	static protected function getContactInfo($context) {
		$pressSettings = $context->getSettings();
		$contactSettingNames = array(
			'mailingAddress', 'contactPhone', 'contactEmail',
			'contactName', 'supportName',
			'supportPhone', 'supportEmail'
		);
		$contactSettings = array_intersect_key($pressSettings, array_fill_keys($contactSettingNames, null));

		// Remove empty elements.
		$contactSettings = array_filter($contactSettings);

		$contactLocalizedSettingNames = array('contactTitle', 'contactAffiliation');

		foreach ($contactLocalizedSettingNames as $settingName) {
			$settingValue = $context->getLocalizedSetting($settingName);
			if ($settingValue) {
				$contactSettings[$settingName] = $settingValue;
			}
		}

		return $contactSettings;
	}

	/**
	 * Get submissions information used by submissions operation.
	 * @param $context Press
	 */
	static protected function getSubmissionsInfo($context) {
		$submissionSettingNames = array('authorGuidelines', 'copyrightNotice');

		$submissionInfo = array();

		foreach ($submissionSettingNames as $settingName) {
			$settingValue = $context->getLocalizedSetting($settingName);
			if ($settingValue) {
				$submissionInfo[$settingName] = $settingValue;
			}
		}

		$submissionChecklist = $context->getLocalizedSetting('submissionChecklist');
		if (!empty($submissionChecklist)) {
			ksort($submissionChecklist);
			reset($submissionChecklist);
			$submissionInfo['checklist'] = $submissionChecklist;
		}

		return $submissionInfo;
	}
}

?>
