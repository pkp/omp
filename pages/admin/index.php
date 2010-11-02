<?php

/**
 * @defgroup pages_admin
 */
 
/**
 * @file pages/admin/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_admin
 * @brief Handle requests for site administration functions. 
 *
 */

// $Id$


switch ($op) {
	//
	// Settings
	//
	case 'settings':
	case 'saveSettings':
		define('HANDLER_CLASS', 'AdminSettingsHandler');
		import('pages.admin.AdminSettingsHandler');
		break;
	//
	// Press Management
	//
	case 'presses':
	case 'createPress':
	case 'editPress':
	case 'updatePress':
	case 'deletePress':
	case 'movePress':
		import('pages.admin.AdminPressHandler');
		define('HANDLER_CLASS', 'AdminPressHandler');
		break;
	//
	// Languages
	//
	case 'languages':
	case 'saveLanguageSettings':
	case 'installLocale':
	case 'uninstallLocale':
	case 'reloadLocale':
	case 'downloadLocale':
		import('pages.admin.AdminLanguagesHandler');
		define('HANDLER_CLASS', 'AdminLanguagesHandler');
		break;
	//
	// Merge users
	//
	case 'mergeUsers':
		import('pages.admin.AdminPeopleHandler');
		define('HANDLER_CLASS', 'AdminPeopleHandler');
		break;
	//
	// Administrative functions
	//
	case 'systemInfo':
	case 'editSystemConfig':
	case 'saveSystemConfig':
	case 'phpInfo':
	case 'expireSessions':
	case 'clearTemplateCache':
	case 'clearDataCache':
		import('pages.admin.AdminFunctionsHandler');
		define('HANDLER_CLASS', 'AdminFunctionsHandler');
		break;
	case 'index':
		define('HANDLER_CLASS', 'AdminHandler');
		import('pages.admin.AdminHandler');
		break;
}

?>
