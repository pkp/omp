<?php

/**
 * @file classes/notification/form/NotificationSettingsForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationSettingsForm
 * @ingroup notification_form
 *
 * @brief Form to edit notification settings.
 */


import('lib.pkp.classes.notification.form.PKPNotificationSettingsForm');

class NotificationSettingsForm extends PKPNotificationSettingsForm {
	/**
	 * Constructor.
	 */
	function NotificationSettingsForm() {
		parent::PKPNotificationSettingsForm();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$userVars = array();
		foreach($this->_getNotificationSettingsMap() as $notificationSetting) {
			$userVars[] = $notificationSetting['settingName'];
			$userVars[] = $notificationSetting['emailSettingName'];
		}

		$this->readUserVars($userVars);
	}

	/**
	 * Get all notification settings form names and their setting type values
	 * @return array
	 */
	function _getNotificationSettingsMap() {
		return array(
			NOTIFICATION_TYPE_SUBMISSION_SUBMITTED => array('settingName' => 'notificationMonographSubmitted',
				'emailSettingName' => 'emailNotificationMonographSubmitted',
				'settingKey' => 'notification.type.submissionSubmitted'),
			NOTIFICATION_TYPE_METADATA_MODIFIED => array('settingName' => 'notificationMetadataModified',
				'emailSettingName' => 'emailNotificationMetadataModified',
				'settingKey' => 'notification.type.metadataModified'),
			NOTIFICATION_TYPE_REVIEWER_COMMENT => array('settingName' => 'notificationReviewerComment',
				'emailSettingName' => 'emailNotificationReviewerComment',
				'settingKey' => 'notification.type.reviewerComment')
		);
	}

	/**
	 * Get a list of notification category names (to display as headers)
	 *  and the notification types under each category
	 * @return array
	 */
	function _getNotificationSettingCategories() {
		return array(
			array('categoryKey' => 'notification.type.submissions',
				'settings' => array(NOTIFICATION_TYPE_SUBMISSION_SUBMITTED, NOTIFICATION_TYPE_METADATA_MODIFIED)),
			array('categoryKey' => 'notification.type.reviewing',
				'settings' => array(NOTIFICATION_TYPE_REVIEWER_COMMENT))
		);
	}

	/**
	 * @copydoc
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('notificationSettingCategories', $this->_getNotificationSettingCategories());
		$templateMgr->assign('notificationSettings',  $this->_getNotificationSettingsMap());
		return parent::fetch($request);
	}

	/**
	 * Save site settings.
	 */
	function execute($request) {
		$user = $request->getUser();
		$userId = $user->getId();
		$press = $request->getPress();

		$blockedNotifications = array();
		$emailSettings = array();
		foreach($this->_getNotificationSettingsMap() as $settingId => $notificationSetting) {
			// Get notifications that the user wants blocked
			if(!$this->getData($notificationSetting['settingName'])) $blockedNotifications[] = $settingId;
			// Get notifications that the user wants to be notified of by email
			if($this->getData($notificationSetting['emailSettingName'])) $emailSettings[] = $settingId;
		}

		$notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
		$notificationSubscriptionSettingsDao->updateNotificationSubscriptionSettings('blocked_notification', $blockedNotifications, $userId, $press->getId());
		$notificationSubscriptionSettingsDao->updateNotificationSubscriptionSettings('blocked_emailed_notification', $emailSettings, $userId, $press->getId());

		return true;
	}


}

?>
