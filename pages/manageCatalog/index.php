<?php

/**
 * @defgroup pages_manageCatalog Catalog management page
 */

/**
 * @file pages/manageCatalog/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 *
 * @brief Handle requests for catalog management functions.
 *
 */

switch ($op) {
    case 'index':
    case 'homepage':
        return new APP\pages\manageCatalog\ManageCatalogHandler();
}
