<?php

/**
 * @defgroup pages_admin Administration page
 */

/**
 * @file pages/admin/index.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_admin
 * @brief Handle requests for site administration functions.
 *
 */

switch ($op) {
	//
	// Site setup
	//
	case 'siteSetup':
	case 'saveSettings':
		define('HANDLER_CLASS', 'AdminSettingsHandler');
		import('pages.admin.AdminSettingsHandler');
		break;
	//
	// Press Management
	//
	case 'contexts':
	case 'startWizard':
		import('lib.pkp.pages.admin.AdminContextHandler');
		define('HANDLER_CLASS', 'AdminContextHandler');
		break;
	//
	// Administrative functions
	//
	case 'systemInfo':
	case 'phpinfo':
	case 'expireSessions':
	case 'clearTemplateCache':
	case 'clearDataCache':
	case 'downloadScheduledTaskLogFile':
	case 'clearScheduledTaskLogFiles':
		import('lib.pkp.pages.admin.AdminFunctionsHandler');
		define('HANDLER_CLASS', 'AdminFunctionsHandler');
		break;
	//
	// Index and administration settings page
	//
	case 'index':
	case 'settings':
		define('HANDLER_CLASS', 'AdminHandler');
		import('lib.pkp.pages.admin.AdminHandler');
		break;
}

?>
