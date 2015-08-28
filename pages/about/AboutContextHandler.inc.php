<?php

/**
 * @file pages/about/AboutContextHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutContextHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for context-level about functions.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.pages.about.IAboutContextInfoProvider');

class AboutContextHandler extends Handler implements IAboutContextInfoProvider {
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
		$templateMgr->assign(AboutContextHandler::getAboutInfo($context));
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
		$templateMgr->assign('editorialTeamInfo', AboutContextHandler::getEditorialTeamInfo($context));
		$templateMgr->display('about/editorialTeam.tpl');
	}

	/**
	 * Display submissions page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, $request) {
		$context = $request->getContext();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionInfo', AboutContextHandler::getSubmissionsInfo($context));
		$templateMgr->display('about/submissions.tpl');
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
		$contactSettingNames = array('mailingAddress', 'contactPhone',
				'contactFax', 'contactEmail', 'contactName', 'supportName',
				'supportPhone', 'supportEmail');
		$contactSettings = array_intersect_key($pressSettings, array_fill_keys($contactSettingNames, null));

		// Remove empty elements.
		$contactSettings = array_filter($contactSettings);

		$contactLocalizedSettingNames = array('contactTitle', 'contactAffiliation', 'contactMailingAddress',
				'contactTitle', 'contactAffiliation', 'contactMailingAddress');

		foreach ($contactLocalizedSettingNames as $settingName) {
			$settingValue = $context->getLocalizedSetting($settingName);
			if ($settingValue) {
				$contactSettings[$settingName] = $settingValue;
			}
		}

		return $contactSettings;
	}

	/**
	 * Get sponsorship information used by sponsorship operation.
	 * @param $context Press
	 * @return Array
	 */
	static protected function getSponsorshipInfo($context) {
		$sponsorshipSettings = array(
				'contributorNote' => $context->getLocalizedSetting('contributorNote'),
				'contributors' => $context->getSetting('contributors'),
				'sponsorNote' => $context->getLocalizedSetting('sponsorNote'),
				'sponsors' => $context->getSetting('sponsors')
		);

		// Remove empty elements.
		$sponsorshipSettings = array_filter($sponsorshipSettings);
		return $sponsorshipSettings;
	}

	/**
	 * Get editorial team information used by editorial team operation.
	 * @param $context Press
	 * @return Array
	 */
	static protected function getEditorialTeamInfo($context) {
		$editorialTeamInfo = array(
				'masthead' => $context->getLocalizedSetting('masthead')
		);

		// Remove empty elements.
		$editorialTeamInfo = array_filter($editorialTeamInfo);
		return $editorialTeamInfo;
	}

	/**
	 * Get editorial policies information used by editorial
	 * policies operation.
	 * @param $context Press
	 * @return Array
	 */
	static protected function getEditorialPoliciesInfo($context) {
		$editorialPoliciesSettingNames = array('focusScopeDesc', 'reviewPolicy',
				'openAccessPolicy', 'customAboutItems');

		$editorialPoliciesInfo = array();

		foreach ($editorialPoliciesSettingNames as $settingName) {
			$settingValue = $context->getLocalizedSetting($settingName);
			if ($settingValue) {
				$editorialPoliciesInfo[$settingName] = $settingValue;
			}
		}

		return $editorialPoliciesInfo;
	}

	/**
	 * Get submissions information used by submissions operation.
	 * @param $context Press
	 */
	static protected function getSubmissionsInfo($context) {
		$submissionSettingNames = array('authorGuidelines', 'copyrightNotice', 'privacyStatement', 'reviewPolicy');

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


	//
	// Static public methods.
	//
	/**
	* @see IAboutContextInfoProvider::getAboutInfo()
	*/
	static function getAboutInfo($context) {
		return array(
			'contact' => AboutContextHandler::getContactInfo($context),
			'description' => $context->getLocalizedSetting('description'),
			'sponsorship' => AboutContextHandler::getSponsorshipInfo($context),
			'editorialTeam' => AboutContextHandler::getEditorialTeamInfo($context),
			'editorialPolicies' => AboutContextHandler::getEditorialPoliciesInfo($context),
			'submissions' => AboutContextHandler::getSubmissionsInfo($context)
		);
	}
}

?>
