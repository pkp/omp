<?php

/**
 * @defgroup pages_authorDashboard Author dashboard page
 */

/**
 * @file pages/authorDashboard/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_authorDashboard
 * @brief Handle requests for the author dashboard.
 *
 */


switch ($op) {
    //
    // Author Dashboard
    //
    case 'submission':
    case 'readSubmissionEmail':
    case 'reviewRoundInfo':
        define('HANDLER_CLASS', 'APP\pages\authorDashboard\AuthorDashboardHandler');
}
