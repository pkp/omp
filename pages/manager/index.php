<?php

/**
 * @defgroup pages_manager
 */

/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions.
 *
 */

// $Id$

switch ($op) {
	//
	// People Management
	//
	case 'people':
	case 'enrollSearch':
	case 'enroll':
	case 'unEnroll':
	case 'showNoRole':
	case 'enrollSyncSelect':
	case 'enrollSync':
	case 'createUser':
	case 'suggestUsername':
	case 'mergeUsers':
	case 'disableUser':
	case 'enableUser':
	case 'removeUser':
	case 'editUser':
	case 'updateUser':
	case 'userProfile':
		import('pages.manager.PeopleHandler');
		define('HANDLER_CLASS', 'PeopleHandler');
		break;
	//
	// E-mail Management
	//
	case 'emails':
	case 'createEmail':
	case 'editEmail':
	case 'updateEmail':
	case 'deleteCustomEmail':
	case 'resetEmail':
	case 'disableEmail':
	case 'enableEmail':
	case 'resetAllEmails':
		import('pages.manager.EmailHandler');
		define('HANDLER_CLASS', 'EmailHandler');
		break;
	//
	// Languages
	//

	case 'languages':
	case 'saveLanguageSettings':
	case 'reloadLocalizedDefaultSettings':
		import('pages.manager.PressLanguagesHandler');
		define('HANDLER_CLASS', 'PressLanguagesHandler');
		break;
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
	//
	// Group Management
	//
	case 'groups':
	case 'createGroup':
	case 'updateGroup':
	case 'deleteGroup':
	case 'editGroup':
	case 'groupMembership':
	case 'addMembership':
	case 'deleteMembership':
	case 'setBoardEnabled':
	case 'moveGroup':
	case 'moveMembership':
		import('pages.manager.GroupHandler');
		define('HANDLER_CLASS', 'GroupHandler');
		break;
	case 'index':
	case 'email':
		define('HANDLER_CLASS', 'ManagerHandler');
		import('pages.manager.ManagerHandler');
		break;
}

?>
