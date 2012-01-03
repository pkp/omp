<?php

/**
 * @file pages/admin/AdminFunctionsHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminFunctionsHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administrative/maintenance functions.
 */

import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionDAO');
import('lib.pkp.classes.site.VersionCheck');
import('pages.admin.AdminHandler');

class AdminFunctionsHandler extends AdminHandler {

	function AdminFunctionsHandler() {
		parent::AdminHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array('systemInfo', 'editSystemConfig', 'saveSystemConfig', 'phpinfo',
			'expireSessions', 'clearTemplateCache', 'clearDataCache')
		);
	}

	/**
	 * Show system information summary.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function systemInfo($args, &$request) {
		$this->setupTemplate($request, true);

		$configData =& Config::getData();

		$dbconn =& DBConnection::getConn();
		$dbServerInfo = $dbconn->ServerInfo();

		$versionDao =& DAORegistry::getDAO('VersionDAO');
		$currentVersion =& $versionDao->getCurrentVersion();
		$versionHistory =& $versionDao->getVersionHistory();

		$serverInfo = array(
			'admin.server.platform' => Core::serverPHPOS(),
			'admin.server.phpVersion' => Core::serverPHPVersion(),
			'admin.server.apacheVersion' => (function_exists('apache_get_version') ? apache_get_version() : __('common.notAvailable')),
			'admin.server.dbDriver' => Config::getVar('database', 'driver'),
			'admin.server.dbVersion' => (empty($dbServerInfo['description']) ? $dbServerInfo['version'] : $dbServerInfo['description'])
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('currentVersion', $currentVersion);
		$templateMgr->assign_by_ref('versionHistory', $versionHistory);
		$templateMgr->assign_by_ref('configData', $configData);
		$templateMgr->assign_by_ref('serverInfo', $serverInfo);
		if ($request->getUserVar('versionCheck')) {
			$latestVersionInfo =& VersionCheck::getLatestVersion();
			$latestVersionInfo['patch'] = VersionCheck::getPatch($latestVersionInfo);
			$templateMgr->assign_by_ref('latestVersionInfo', $latestVersionInfo);
		}
		$templateMgr->assign('helpTopicId', 'site.administrativeFunctions');
		$templateMgr->display('admin/systemInfo.tpl');
	}

	/**
	 * Show full PHP configuration information.
	 */
	function phpinfo() {
		phpinfo();
	}

	/**
	 * Expire all user sessions (will log out all users currently logged in).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function expireSessions($args, &$request) {
		$sessionDao =& DAORegistry::getDAO('SessionDAO');
		$sessionDao->deleteAllSessions();
		$request->redirect(null, 'admin');
	}

	/**
	 * Clear compiled templates.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function clearTemplateCache($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->clearTemplateCache();
		$request->redirect(null, 'admin');
	}

	/**
	 * Clear the data cache.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function clearDataCache($args, &$request) {
		// Clear the CacheManager's caches
		$cacheManager =& CacheManager::getManager();
		$cacheManager->flush();

		// Clear ADODB's cache
		$userDao =& DAORegistry::getDAO('UserDAO'); // As good as any
		$userDao->flushCache();

		$request->redirect(null, 'admin');
	}
}

?>
