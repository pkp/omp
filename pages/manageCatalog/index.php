<?php

/**
 * @defgroup pages_manageCatalog
 */

/**
 * @file pages/manageCatalog/index.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 * @brief Handle requests for catalog management functions.
 *
 */

switch ($op) {
	case 'index':
	case 'features':
	case 'newReleases':
	case 'getCategories':
	case 'category':
	case 'getSeries':
	case 'series':
	case 'search':
	case 'setFeatured':
		define('HANDLER_CLASS', 'ManageCatalogHandler');
		import('pages.manageCatalog.ManageCatalogHandler');
		break;
}

?>
