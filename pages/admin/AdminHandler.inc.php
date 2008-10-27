<?php

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administration functions. 
 */

// $Id$


import('core.PKPHandler');

class AdminHandler extends PKPHandler {

	/**
	 * Display site admin index page.
	 */
	function index() {
		AdminHandler::validate();
		AdminHandler::setupTemplate();

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.index');
		$templateMgr->display('admin/index.tpl');
	}

	/**
	 * Validate that user has admin privileges and is not trying to access the admin module with a press selected.
	 * Redirects to the user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		if (!Validation::isSiteAdmin() || Request::getRequestedPressPath() != 'index') {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_ADMIN));
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'admin'), 'admin.siteAdmin'))
				: array(array(Request::url(null, 'user'), 'navigation.user'))
		);
	}


	//
	// Settings
	//

	function settings() {
		import('pages.admin.AdminSettingsHandler');
		AdminSettingsHandler::settings();
	}

	function saveSettings() {
		import('pages.admin.AdminSettingsHandler');
		AdminSettingsHandler::saveSettings();
	}


	//
	// Press Management
	//

	function presses() {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::presses();
	}

	function createPress() {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::createPress();
	}

	function editPress($args = array()) {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::editPress($args);
	}

	function updatePress() {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::updatePress();
	}

	function deletePress($args) {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::deletePress($args);
	}

	function movePress() {
		import('pages.admin.AdminPressHandler');
		AdminPressHandler::movePress();
	}


	//
	// Languages
	//

	function languages() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::languages();
	}

	function saveLanguageSettings() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::saveLanguageSettings();
	}

	function installLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::installLocale();
	}

	function uninstallLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::uninstallLocale();
	}

	function reloadLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::reloadLocale();
	}

	function downloadLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::downloadLocale();
	}


	//
	// Authentication sources
	//

	function auth() {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::auth();
	}

	function updateAuthSources() {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::updateAuthSources();
	}

	function createAuthSource() {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::createAuthSource();
	}

	function editAuthSource($args) {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::editAuthSource($args);
	}

	function updateAuthSource($args) {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::updateAuthSource($args);
	}

	function deleteAuthSource($args) {
		import('pages.admin.AuthSourcesHandler');
		AuthSourcesHandler::deleteAuthSource($args);
	}


	//
	// Merge users
	//

	function mergeUsers($args) {
		import('pages.admin.AdminPeopleHandler');
		AdminPeopleHandler::mergeUsers($args);
	}


	//
	// Administrative functions
	//

	function systemInfo() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::systemInfo();
	}

	function editSystemConfig() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::editSystemConfig();
	}

	function saveSystemConfig() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::saveSystemConfig();
	}

	function phpinfo() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::phpInfo();
	}

	function expireSessions() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::expireSessions();
	}

	function clearTemplateCache() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::clearTemplateCache();
	}

	function clearDataCache() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::clearDataCache();
	}
}

?>
