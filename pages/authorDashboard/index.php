<?php

/**
 * @defgroup pages_authorDashboard Author dashboard page
 */

/**
 * @file pages/authorDashboard/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
		import('pages.authorDashboard.AuthorDashboardHandler');
		define('HANDLER_CLASS', 'AuthorDashboardHandler');
}

?>
