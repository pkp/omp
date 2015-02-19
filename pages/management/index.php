<?php

/**
 * @defgroup pages_management Management pages
 */

/**
 * @file pages/management/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_management
 * @brief Handle requests for management pages.
 *
 */


switch ($op) {
	//
	// Settings
	//
	case 'categories':
	case 'importExport':
	case 'series':
	case 'settings':
		import('pages.management.SettingsHandler');
		define('HANDLER_CLASS', 'SettingsHandler');
		break;
	case 'tools':
		import('pages.management.ToolsHandler');
		define('HANDLER_CLASS', 'ToolsHandler');
		break;
	case 'navigation':
		import('pages.management.NavigationHandler');
		define('HANDLER_CLASS', 'NavigationHandler');
		break;
}
?>
