<?php

/**
 * @defgroup pages_manager
 */

/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions.
 *
 */


switch ($op) {
	//
	// Files Browser
	//
	case 'files':
	case 'fileUpload':
	case 'fileMakeDir':
	case 'fileDelete':
		import('pages.manager.FilesHandler');
		define('HANDLER_CLASS', 'FilesHandler');
		break;
	//
	// Announcement Types
	//
	case 'announcementTypes':
	case 'deleteAnnouncementType':
	case 'createAnnouncementType':
	case 'editAnnouncementType':
	case 'updateAnnouncementType':
	//
	// Announcements
	//
	case 'announcements':
	case 'deleteAnnouncement':
	case 'createAnnouncement':
	case 'editAnnouncement':
	case 'updateAnnouncement':
		import('pages.manager.AnnouncementHandler');
		define('HANDLER_CLASS', 'AnnouncementHandler');
		break;
	//
	// Import/Export
	//
	case 'importexport':
		import('pages.manager.ImportExportHandler');
		define('HANDLER_CLASS', 'ImportExportHandler');
		break;
	//
	// Plugin Management
	//
	case 'plugins':
	case 'plugin':
		define('HANDLER_CLASS', 'PluginHandler');
		import('pages.manager.PluginHandler');
		break;
	case 'managePlugins':
		define('HANDLER_CLASS', 'PluginManagementHandler');
		import('pages.manager.PluginManagementHandler');
		break;
	case 'index':
		define('HANDLER_CLASS', 'ManagerHandler');
		import('pages.manager.ManagerHandler');
		break;
}

?>
