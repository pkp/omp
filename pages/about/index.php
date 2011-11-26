<?php

/**
 * @defgroup pages_about
 */

/**
 * @file pages/about/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_about
 * @brief Handle requests for about the press functions.
 *
 */

switch ($op) {
	case 'index':
	case 'contact':
	case 'pressSponsorship':
	case 'editorialTeam':
	case 'editorialTeamBio':
	case 'editorialPolicies':
	case 'submissions':
	case 'siteMap':
	case 'aboutThisPublishingSystem':
		define('HANDLER_CLASS', 'AboutHandler');
		import('pages.about.AboutHandler');
		break;
}

?>
