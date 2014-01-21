<?php

/**
 * @defgroup pages_announcement Announcement page
 */

/**
 * @file pages/announcement/index.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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
		import('pages.announcement.AnnouncementHandler');
		break;
}

?>
