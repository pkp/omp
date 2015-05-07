<?php

/**
 * @file classes/notification/form/NotificationSettingsForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
		foreach($this->getNotificationSettingsMap() as $notificationSetting) {
			$userVars[] = $notificationSetting['settingName'];
			$userVars[] = $notificationSetting['emailSettingName'];
		}

		$this->readUserVars($userVars);
	}

	/**
	 * @copydoc PKPNotificationSettingsForm::getNotificationSettingsMap()
	 */
	protected function getNotificationSettingsMap() {
		return parent::getNotificationSettingsMap() + array(
			NOTIFICATION_TYPE_SUBMISSION_SUBMITTED => array('settingName' => 'notificationMonographSubmitted',
				'emailSettingName' => 'emailNotificationMonographSubmitted',
				'settingKey' => 'notification.type.submissionSubmitted'),
			NOTIFICATION_TYPE_METADATA_MODIFIED => array('settingName' => 'notificationMetadataModified',
				'emailSettingName' => 'emailNotificationMetadataModified',
				'settingKey' => 'notification.type.metadataModified'),
			NOTIFICATION_TYPE_REVIEWER_COMMENT => array('settingName' => 'notificationReviewerComment',
				'emailSettingName' => 'emailNotificationReviewerComment',
				'settingKey' => 'notification.type.reviewerComment'),
			NOTIFICATION_TYPE_AUDITOR_REQUEST => array('settingName' => 'notificationAuditorRequest',
				'emailSettingName' => 'emailNotificationAuditorRequest',
				'settingKey' => 'notification.type.auditorRequest'),
		);
	}

	/**
	 * @copydoc PKPNotificationSettingsForm::getNotificationSettingsCategories()
	 */
	protected function getNotificationSettingCategories() {
		$parentCategories = parent::getNotificationSettingsCategories();
		$parentCategories[0]['settings'][] = NOTIFICATION_TYPE_REVIEWER_COMMENT;

		$categories = array_merge($parentCategories, array(array(
			'categoryKey' => 'notification.type.submissions',
			'settings' => array(NOTIFICATION_TYPE_SUBMISSION_SUBMITTED, NOTIFICATION_TYPE_METADATA_MODIFIED, NOTIFICATION_TYPE_AUDITOR_REQUEST))
		));

		return $categories;	
	}

	/**
	 * @copydoc PKPNotificationSettingsForm::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('notificationSettingCategories', $this->getNotificationSettingCategories());
		$templateMgr->assign('notificationSettings',  $this->getNotificationSettingsMap());
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
		foreach($this->getNotificationSettingsMap() as $settingId => $notificationSetting) {
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
