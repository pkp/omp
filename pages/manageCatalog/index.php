<?php

/**
 * @defgroup pages_manageCatalog Catalog management page
 */

/**
 * @file pages/manageCatalog/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 * @brief Handle requests for catalog management functions.
 *
 */

switch ($op) {
	case 'index':
	case 'homepage':
		define('HANDLER_CLASS', 'ManageCatalogHandler');
		import('pages.manageCatalog.ManageCatalogHandler');
		break;
}


