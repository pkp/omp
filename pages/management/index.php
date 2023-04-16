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
        define('HANDLER_CLASS', 'APP\pages\management\SettingsHandler');
        break;
    case 'tools':
    case 'importexport':
    case 'statistics':
    case 'permissions':
    case 'resetPermissions':
        define('HANDLER_CLASS', 'PKP\pages\management\PKPToolsHandler');
        break;
    case 'navigation':
        define('HANDLER_CLASS', 'APP\pages\management\NavigationHandler');
        break;
}
