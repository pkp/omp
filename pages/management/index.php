<?php

/**
 * @defgroup pages_management Management pages
 */

/**
 * @file pages/management/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_management
 *
 * @brief Handle requests for management pages.
 *
 */

switch ($op) {
    //
    // Settings
    //
    case 'categories':
    case 'series':
    case 'settings':
    case 'access':
        return new APP\pages\management\SettingsHandler();
    case 'tools':
    case 'importexport':
    case 'statistics':
    case 'permissions':
    case 'resetPermissions':
        return new PKP\pages\management\PKPToolsHandler();
    case 'navigation':
        return new APP\pages\management\NavigationHandler();
}
