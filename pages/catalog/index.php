<?php

/**
 * @defgroup pages_catalog
 */

/**
 * @file pages/catalog/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 * @brief Handle requests for the public catalog view.
 *
 */

switch ($op) {
	case 'index':
	case 'category':
	case 'series':
	case 'book':
		define('HANDLER_CLASS', 'CatalogHandler');
		import('pages.catalog.CatalogHandler');
		break;
}

?>
