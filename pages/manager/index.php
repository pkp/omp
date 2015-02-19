<?php

/**
 * @defgroup pages_manager Manager page
 */

/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions.
 *
 */


switch ($op) {
	//
	// Import/Export
	//
	case 'importexport':
		import('pages.manager.ImportExportHandler');
		define('HANDLER_CLASS', 'ImportExportHandler');
		break;
}

?>
