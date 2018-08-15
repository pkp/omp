<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base ManagementHandler.
import('lib.pkp.pages.management.ManagementHandler');

class SettingsHandler extends ManagementHandler {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array(
				'access',
			)
		);
		$this->addRoleAssignment(
			ROLE_ID_MANAGER,
			array(
				'categories',
				'series',
				'settings',
				'access',
				'press',
				'website',
				'publication',
				'distribution',
			)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Display categories admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function categories($args, $request) {
		$this->setupTemplate($request);

		$templateMgr = TemplateManager::getManager($request);
		$jsonMessage = new JSONMessage(true, $templateMgr->fetch('management/categories.tpl'));
		return $jsonMessage->getString();
	}

	/**
	 * Display series admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function series($args, $request) {
		$this->setupTemplate($request);

		$templateMgr = TemplateManager::getManager($request);
		$jsonMessage = new JSONMessage(true, $templateMgr->fetch('management/series.tpl'));
		return $jsonMessage->getString();
	}

	/**
	 * Route to other settings operations.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function settings($args, $request) {
		$path = array_shift($args);
		switch($path) {
			case 'access':
				$this->access($args, $request);
				break;
			case 'index':
			case '':
			case 'context':
				$this->press($args, $request);
				break;
			case 'website':
				$this->website($args, $request);
				break;
			case 'publication':
				$this->publication($args, $request);
				break;
			case 'distribution':
				$this->distribution($args, $request);
				break;
			default:
				assert(false);
		}
	}

	/**
	 * Display Access and Security page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function access($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('management/settings/access.tpl');
	}

	/**
	 * Display The Press page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function press($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);

		// Display a warning message if there is a new version of OMP available
		if (Config::getVar('general', 'show_upgrade_warning')) {
			import('lib.pkp.classes.site.VersionCheck');
			if ($latestVersion = VersionCheck::checkIfNewVersionExists()) {
				$templateMgr->assign('newVersionAvailable', true);
				$templateMgr->assign('latestVersion', $latestVersion);
				$currentVersion = VersionCheck::getCurrentDBVersion();
				$templateMgr->assign('currentVersion', $currentVersion->getVersionString());

				// Get contact information for site administrator
				$roleDao = DAORegistry::getDAO('RoleDAO');
				$siteAdmins = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN);
				$templateMgr->assign('siteAdmin', $siteAdmins->next());
			}
		}
		$templateMgr->display('management/settings/press.tpl');
	}

	/**
	 * Display website page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function website($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$press = $request->getPress();
		$templateMgr->assign('enableAnnouncements', $press->getSetting('enableAnnouncements'));
		$templateMgr->display('management/settings/website.tpl');
	}

	/**
	 * Display publication process page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function publication($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('management/settings/workflow.tpl');
	}

	/**
	 * Display distribution process page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function distribution($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION); // submission.permissions
		$templateMgr->display('management/settings/distribution.tpl');
	}
}


