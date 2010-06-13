<?php

/**
 * @defgroup pages_manager
 */

/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions.
 *
 */

switch ($op) {
	//
	// Setup
	//
	case 'setup':
	case 'saveSetup':
	case 'setupSaved':
	case 'downloadLayoutTemplate':
		import('pages.settings.SetupHandler');
		define('HANDLER_CLASS', 'SetupHandler');
		break;
	default:
		import('pages.settings.SettingsHandler');
		define('HANDLER_CLASS', 'SettingsHandler');
}

?>
