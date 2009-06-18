<?php

/**
 * @defgroup pages_manager
 */
 
/**
 * @file pages/manager/index.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_manager
 * @brief Handle requests for press management functions. 
 *
 */

// $Id$

switch ($op) {
	//
	// Review Setup
	//
	case 'reviewSignoff':
	case 'selectSignoffUser':
	case 'selectSignoffGroup':
	case 'addSignoffGroup':
	case 'addSignoffUser':
	case 'removeSignoffGroup':
	case 'removeSignoffUser':
		import('pages.manager.ReviewSetupHandler');
		define('HANDLER_CLASS', 'ReviewSetupHandler');
		break;
	//
	// Setup
	//
	case 'setup':
	case 'saveSetup':
	case 'setupSaved':
	case 'downloadLayoutTemplate':
		import('pages.manager.SetupHandler');
		define('HANDLER_CLASS', 'SetupHandler');
		break;
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
	case 'signInAsUser':
	case 'signOutAsUser':
		import('pages.manager.PeopleHandler');
		define('HANDLER_CLASS', 'PeopleHandler');
		break;
	//
	// Series Management
	//
	case 'series':
	case 'createSeries':
	case 'editSeries':
	case 'updateSeries':
	case 'deleteSeries':
	case 'moveSeries':
	//
	// Submission Category Management
	//
	case 'submissionCategory':
	case 'createSubmissionCategory':
	case 'editSubmissionCategory':
	case 'updateSubmissionCategory':
	case 'deleteSubmissionCategory':
	case 'moveSubmissionCategory':
		import('pages.manager.AcquisitionsArrangementHandler');
		define('HANDLER_CLASS', 'AcquisitionsArrangementHandler');
		break;
	//
	// Review Form Management
	//
	case 'reviewForms':
	case 'createReviewForm':
	case 'editReviewForm':
	case 'updateReviewForm':
	case 'previewReviewForm':
	case 'deleteReviewForm':
	case 'activateReviewForm':
	case 'deactivateReviewForm':
	case 'copyReviewForm':
	case 'moveReviewForm':
	case 'reviewFormElements':
	case 'createReviewFormElement':
	case 'editReviewFormElement':
	case 'deleteReviewFormElement':
	case 'updateReviewFormElement':
	case 'moveReviewFormElement':
	case 'copyReviewFormElement':
		import('pages.manager.ReviewFormHandler');
		define('HANDLER_CLASS', 'ReviewFormHandler');
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
	// Subscription Policies 
	//
	case 'subscriptionPolicies':
	case 'saveSubscriptionPolicies':
	//
	// Subscription Types
	//
	case 'subscriptionTypes':
	case 'deleteSubscriptionType':
	case 'createSubscriptionType':
	case 'selectSubscriber':
	case 'editSubscriptionType':
	case 'updateSubscriptionType':
	case 'moveSubscriptionType':
	//
	// Subscriptions
	//

	case 'subscriptions':
	case 'deleteSubscription':
	case 'createSubscription':
	case 'editSubscription':
	case 'updateSubscription':
		import('pages.manager.SubscriptionHandler');
		define('HANDLER_CLASS', 'SubscriptionHandler');
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
		import('pages.manager.PluginHandler');
		define('HANDLER_CLASS', 'PluginHandler');
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
	default:
		define('HANDLER_CLASS', 'ManagerHandler');
		import('pages.manager.ManagerHandler');
		break;
}

?>
