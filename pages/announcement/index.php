<?php

/**
 * @defgroup pages_announcement Announcement page
 */

/**
 * @file pages/announcement/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Handle requests for public announcement functions.
 *
 * @ingroup pages_announcement
 * @brief Handle requests for public announcement functions.
 *
 */


switch ($op) {
	case 'index':
	case 'view':
		define('HANDLER_CLASS', 'AnnouncementHandler');
		import('lib.pkp.pages.announcement.AnnouncementHandler');
		break;
}

?>
