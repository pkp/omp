<?php

/**
 * @file ManagerHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for press management functions. 
 */

// $Id$


import('handler.Handler');

class ManagerHandler extends Handler {

	/**
	 * Display press management index page.
	 */
	function index() {
		ManagerHandler::validate();
		ManagerHandler::setupTemplate();

		$press =& Request::getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$subscriptionsEnabled = $pressSettingsDao->getSetting($press->getId(), 'enableSubscriptions'); 
		$announcementsEnabled = $pressSettingsDao->getSetting($press->getId(), 'enableAnnouncements'); 

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('subscriptionsEnabled', $subscriptionsEnabled);

		$session =& Request::getSession();
		$session->unsetSessionVar('enrolmentReferrer');

		$templateMgr->assign('announcementsEnabled', $announcementsEnabled);
		$templateMgr->assign('helpTopicId','press.index');
		$templateMgr->display('manager/index.tpl');
	}

	/**
	 * Send an email to a user or group of users.
	 */
	function email($args) {
		parent::validate();

		ManagerHandler::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.users.emailUsers');

		$userDao =& DAORegistry::getDAO('UserDAO');

		$site =& Request::getSite();
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('mail.MailTemplate');
		$email =& new MailTemplate(Request::getUserVar('template'), Request::getUserVar('locale'));

		if (Request::getUserVar('send') && !$email->hasErrors()) {
			$email->send();
			Request::redirect(null, Request::getRequestedPage());
		} else {
			$email->assignParams(); // FIXME Forces default parameters to be assigned (should do this automatically in MailTemplate?)
			if (!Request::getUserVar('continued')) {
				if (($groupId = Request::getUserVar('toGroup')) != '') {
					// Special case for emailing entire groups:
					// Check for a group ID and add recipients.
					$groupDao =& DAORegistry::getDAO('GroupDAO');
					$group =& $groupDao->getGroup($groupId);
					if ($group && $group->getPressId() == $press->getId()) {
						$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
						$memberships =& $groupMembershipDao->getMemberships($group->getGroupId());
						$memberships =& $memberships->toArray();
						foreach ($memberships as $membership) {
							$user =& $membership->getUser();
							$email->addRecipient($user->getEmail(), $user->getFullName());
						}
					}
				}
				if (count($email->getRecipients())==0) $email->addRecipient($user->getEmail(), $user->getFullName());
			}
			$email->displayEditForm(Request::url(null, null, 'email'), array(), 'manager/people/email.tpl');
		}
	}
	//
	// Review Setup
	//
	function reviewSignoffs($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::reviewSignoffs($args);
	}
	function viewSignoffEntities($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::viewSignoffEntities($args);
	}
	function addSignoffGroup($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::addSignoffGroup($args);
	}
	function removeSignoffGroup($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::removeSignoffGroup($args);
	}
	function addSignoffUser($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::addSignoffUser($args);
	}
	function removeSignoffUser($args) {
		import('pages.manager.ReviewSetupHandler');
		ReviewSetupHandler::removeSignoffUser($args);
	}
	/**
	 * Validate that user has permissions to manage the selected press.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		$press =& Request::getPress();
		if (!$press || (!Validation::isPressManager() && !Validation::isSiteAdmin())) {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'manager'), 'manager.pressManagement'))
				: array(array(Request::url(null, 'user'), 'navigation.user'))
		);
	}


	//
	// Setup
	//

	function setup($args) {
		import('pages.manager.SetupHandler');
		SetupHandler::setup($args);
	}

	function saveSetup($args) {
		import('pages.manager.SetupHandler');
		SetupHandler::saveSetup($args);
	}

	function setupSaved($args) {
		import('pages.manager.SetupHandler');
		SetupHandler::setupSaved($args);
	}

	function downloadLayoutTemplate($args) {
		import('pages.manager.SetupHandler');
		SetupHandler::downloadLayoutTemplate($args);
	}

	//
	// People Management
	//

	function people($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::people($args);
	}

	function enrollSearch($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::enrollSearch($args);
	}

	function enroll($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::enroll($args);
	}

	function unEnroll($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::unEnroll($args);
	}

	function enrollSyncSelect($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::enrollSyncSelect($args);
	}

	function enrollSync($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::enrollSync($args);
	}

	function createUser() {
		import('pages.manager.PeopleHandler');
		PeopleHandler::createUser();
	}

	function suggestUsername() {
		import('pages.manager.PeopleHandler');
		PeopleHandler::suggestUsername();
	}

	function mergeUsers($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::mergeUsers($args);
	}

	function disableUser($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::disableUser($args);
	}

	function enableUser($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::enableUser($args);
	}

	function removeUser($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::removeUser($args);
	}

	function editUser($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::editUser($args);
	}

	function updateUser() {
		import('pages.manager.PeopleHandler');
		PeopleHandler::updateUser();
	}

	function userProfile($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::userProfile($args);
	}

	function signInAsUser($args) {
		import('pages.manager.PeopleHandler');
		PeopleHandler::signInAsUser($args);
	}

	function signOutAsUser() {
		import('pages.manager.PeopleHandler');
		PeopleHandler::signOutAsUser();
	}


	//
	// Series Management
	//

	function series() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::listItems();
	}

	function createSeries() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::createItem();
	}

	function editSeries($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::editItem($args);
	}

	function updateSeries($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::updateItem($args);
	}

	function deleteSeries($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::deleteItem($args);
	}

	function moveSeries() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::moveItem();
	}

	//
	// Submission Category Management
	//

	function submissionCategory() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::listItems(CATEGORY_ARRANGEMENT);
	}

	function createSubmissionCategory() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::createItem(CATEGORY_ARRANGEMENT);
	}

	function editSubmissionCategory($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::editItem($args, CATEGORY_ARRANGEMENT);
	}

	function updateSubmissionCategory($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::updateItem($args, CATEGORY_ARRANGEMENT);
	}

	function deleteSubmissionCategory($args) {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::deleteItem($args, CATEGORY_ARRANGEMENT);
	}

	function moveSubmissionCategory() {
		import('pages.manager.AcquisitionsArrangementHandler');
		AcquisitionsArrangementHandler::moveItem(CATEGORY_ARRANGEMENT);
	}

	//
	// Review Form Management
	//

	function reviewForms() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::reviewForms();
	}

	function createReviewForm() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::createReviewForm();
	}

	function editReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::editReviewForm($args);
	}

	function updateReviewForm() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::updateReviewForm();
	}

	function previewReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::previewReviewForm($args);
	}

	function deleteReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::deleteReviewForm($args);
	}

	function activateReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::activateReviewForm($args);
	}

	function deactivateReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::deactivateReviewForm($args);
	}

	function copyReviewForm($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::copyReviewForm($args);
	}

	function moveReviewForm() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::moveReviewForm();
	}

	function reviewFormElements($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::reviewFormElements($args);
	}

	function createReviewFormElement($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::createReviewFormElement($args);
	}

	function editReviewFormElement($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::editReviewFormElement($args);
	}

	function deleteReviewFormElement($args) {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::deleteReviewFormElement($args);
	}

	function updateReviewFormElement() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::updateReviewFormElement();
	}

	function moveReviewFormElement() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::moveReviewFormElement();
	}
	
	function copyReviewFormElement() {
		import('pages.manager.ReviewFormHandler');
		ReviewFormHandler::copyReviewFormElement();
	}


	//
	// E-mail Management
	//

	function emails($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::emails($args);
	}

	function createEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::createEmail($args);
	}

	function editEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::editEmail($args);
	}

	function updateEmail() {
		import('pages.manager.EmailHandler');
		EmailHandler::updateEmail();
	}

	function deleteCustomEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::deleteCustomEmail($args);
	}

	function resetEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::resetEmail($args);
	}

	function disableEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::disableEmail($args);
	}

	function enableEmail($args) {
		import('pages.manager.EmailHandler');
		EmailHandler::enableEmail($args);
	}

	function resetAllEmails() {
		import('pages.manager.EmailHandler');
		EmailHandler::resetAllEmails();
	}


	//
	// Languages
	//

	function languages() {
		import('pages.manager.PressLanguagesHandler');
		PressLanguagesHandler::languages();
	}

	function saveLanguageSettings() {
		import('pages.manager.PressLanguagesHandler');
		PressLanguagesHandler::saveLanguageSettings();
	}
	
	function reloadLocalizedDefaultSettings() {
		import('pages.manager.PressLanguagesHandler');
		PressLanguagesHandler::reloadLocalizedDefaultSettings();
	}


	//
	// Files Browser
	//

	function files($args) {
		import('pages.manager.FilesHandler');
		FilesHandler::files($args);
	}

	function fileUpload($args) {
		import('pages.manager.FilesHandler');
		FilesHandler::fileUpload($args);
	}

	function fileMakeDir($args) {
		import('pages.manager.FilesHandler');
		FilesHandler::fileMakeDir($args);
	}

	function fileDelete($args) {
		import('pages.manager.FilesHandler');
		FilesHandler::fileDelete($args);
	}


	//
	// Subscription Policies 
	//

	function subscriptionPolicies() {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::subscriptionPolicies();
	}

	function saveSubscriptionPolicies($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::saveSubscriptionPolicies($args);
	}


	//
	// Subscription Types
	//

	function subscriptionTypes() {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::subscriptionTypes();
	}

	function deleteSubscriptionType($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::deleteSubscriptionType($args);
	}

	function createSubscriptionType() {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::createSubscriptionType();
	}

	function selectSubscriber($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::selectSubscriber($args);
	}

	function editSubscriptionType($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::editSubscriptionType($args);
	}

	function updateSubscriptionType($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::updateSubscriptionType($args);
	}

	function moveSubscriptionType($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::moveSubscriptionType($args);
	}


	//
	// Subscriptions
	//

	function subscriptions() {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::subscriptions();
	}

	function deleteSubscription($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::deleteSubscription($args);
	}

	function createSubscription() {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::createSubscription();
	}

	function editSubscription($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::editSubscription($args);
	}

	function updateSubscription($args) {
		import('pages.manager.SubscriptionHandler');
		SubscriptionHandler::updateSubscription($args);
	}


	//
	// Announcement Types 
	//

	function announcementTypes() {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::announcementTypes();
	}

	function deleteAnnouncementType($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::deleteAnnouncementType($args);
	}

	function createAnnouncementType() {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::createAnnouncementType();
	}

	function editAnnouncementType($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::editAnnouncementType($args);
	}

	function updateAnnouncementType($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::updateAnnouncementType($args);
	}


	//
	// Announcements 
	//

	function announcements() {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::announcements();
	}

	function deleteAnnouncement($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::deleteAnnouncement($args);
	}

	function createAnnouncement() {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::createAnnouncement();
	}

	function editAnnouncement($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::editAnnouncement($args);
	}

	function updateAnnouncement($args) {
		import('pages.manager.AnnouncementHandler');
		AnnouncementHandler::updateAnnouncement($args);
	}

	//
	// Import/Export
	//

	function importexport($args) {
		import('pages.manager.ImportExportHandler');
		ImportExportHandler::importExport($args);
	}

	//
	// Plugin Management
	//

	function plugins($args) {
		import('pages.manager.PluginHandler');
		PluginHandler::plugins($args);
	}

	function plugin($args) {
		import('pages.manager.PluginHandler');
		PluginHandler::plugin($args);
	}

	//
	// Group Management
	//

	function groups($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::groups($args);
	}

	function createGroup($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::createGroup($args);
	}

	function updateGroup($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::updateGroup($args);
	}

	function deleteGroup($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::deleteGroup($args);
	}

	function editGroup($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::editGroup($args);
	}

	function groupMembership($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::groupMembership($args);
	}

	function addMembership($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::addMembership($args);
	}

	function deleteMembership($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::deleteMembership($args);
	}

	function setBoardEnabled($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::setBoardEnabled($args);
	}

	function moveGroup($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::moveGroup($args);
	}

	function moveMembership($args) {
		import('pages.manager.GroupHandler');
		GroupHandler::moveMembership($args);
	}

	//
	// Statistics Functions
	//

	function statistics($args) {
		import('pages.manager.StatisticsHandler');
		StatisticsHandler::statistics($args);
	}

	function saveStatisticsSections() {
		import('pages.manager.StatisticsHandler');
		StatisticsHandler::saveStatisticsSections();
	}

	function savePublicStatisticsList() {
		import('pages.manager.StatisticsHandler');
		StatisticsHandler::savePublicStatisticsList();
	}

	function report($args) {
		import('pages.manager.StatisticsHandler');
		StatisticsHandler::report($args);
	}

}

?>
