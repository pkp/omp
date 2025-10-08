<?php

/**
 * @defgroup pages_catalog Catalog page
 */

/**
 * @file pages/catalog/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the public catalog view.
 *
 */

switch ($op) {
    case 'index':
    case 'page':
    case 'fullSize':
    case 'newReleases':
    case 'series':
    case 'thumbnail':
    case 'results':
        return new APP\pages\catalog\CatalogHandler();
    case 'category':
        return new PKP\pages\publication\PKPCategoryHandler;
    case 'book':
    case 'download':
    case 'view':
        return new APP\pages\catalog\CatalogBookHandler();
}
