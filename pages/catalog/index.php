<?php

/**
 * @defgroup pages_catalog Catalog page
 */

/**
 * @file pages/catalog/index.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 * @brief Handle requests for the public catalog view.
 *
 */

switch ($op) {
	case 'index':
	case 'category':
	case 'fullSize':
	case 'newReleases':
	case 'series':
	case 'thumbnail':
	case 'results':
		define('HANDLER_CLASS', 'CatalogHandler');
		import('pages.catalog.CatalogHandler');
		break;
	case 'book':
	case 'download':
	case 'view':
		define('HANDLER_CLASS', 'CatalogBookHandler');
		import('pages.catalog.CatalogBookHandler');
		break;
}

?>
