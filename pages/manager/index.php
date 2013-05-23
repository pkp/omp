<?php

/**
 * @defgroup pages_manager
 */

/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions.
 *
 */


switch ($op) {
	//
	// Files Browser
	//
	case 'files':
	case 'fileUpload':
	case 'fileMakeDir':
	case 'fileDelete':
		import('pages.manager.FilesHandler');
		define('HANDLER_CLASS', 'FilesHandler');
		break;
	//
	// Import/Export
	//
	case 'importexport':
		import('pages.manager.ImportExportHandler');
		define('HANDLER_CLASS', 'ImportExportHandler');
		break;
	//
	// Plugin Management
	//
	case 'plugin':
		define('HANDLER_CLASS', 'PluginHandler');
		import('lib.pkp.pages.manager.PluginHandler');
		break;
}

?>
