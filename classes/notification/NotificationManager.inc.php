<?php

/**
 * @file classes/notification/NotificationManager.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPNotificationManager
 * @ingroup notification
 * @see NotificationDAO
 * @see Notification
 * @brief Class for Notification Manager.
 */

import('lib.pkp.classes.notification.PKPNotificationManager');

class NotificationManager extends PKPNotificationManager {
	/**
	 * Constructor.
	 */
	function NotificationManager() {
		parent::PKPNotificationManager();
	}


	//
	// Public methods.
	//
	/**
	 * @copydoc PKPNotificationManager::getNotificationUrl()
	 */
	public function getNotificationUrl($request, $notification) {
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();
		$contextDao = Application::getContextDAO();
		$context = $contextDao->getById($notification->getContextId());

		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'manageCatalog');
		}

		return parent::getNotificationUrl($request, $notification);
	}

	/**
	 * @copydoc PKPNotificationManager::getNotificationMessage()
	 */
	public function getNotificationMessage($request, $notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.formatNeedsApprovedSubmission');
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.visitCatalog');
		}
		return parent::getNotificationMessage($request, $notification);
	}

	/**
	 * @copydoc PKPNotificationManager::getNotificationTitle()
	 */
	public function getNotificationTitle($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				return __('notification.type.approveSubmissionTitle');
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				return __('notification.type.visitCatalogTitle');
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound = $reviewRoundDao->getById($notification->getAssocId());
				return __('notification.type.roundStatusTitle', array('round' => $reviewRound->getRound()));
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				return __('notification.type.configurePaymentMethod.title');
		}
		return parent::getNotificationTitle($notification);
	}

	/**
	 * @copydoc PKPNotificationManager::getIconClass()
	 */
	public function getIconClass($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				return 'notifyIconNewComment';
		}
		return parent::getIconClass($notification);
	}

	/**
	 * @copydoc PKPNotificationManager::getStyleClass()
	 */
	public function getStyleClass($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
			case NOTIFICATION_TYPE_INDEX_ASSIGNMENT:
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				return NOTIFICATION_STYLE_CLASS_WARNING;
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
			case NOTIFICATION_TYPE_VISIT_CATALOG:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				return NOTIFICATION_STYLE_CLASS_INFORMATION;
		}
		return parent::getStyleClass($notification);
	}

	/**
	 * @copydoc PKPNotificationManager::isVisibleToAllUsers()
	 */
	public function isVisibleToAllUsers($notificationType, $assocType, $assocId) {
		switch ($notificationType) {
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				return true;
			default:
				return parent::isVisibleToAllUsers($notificationType, $assocType, $assocId);
		}
	}

	/**
	 * @copydoc PKPNotificationManager::getMgrDelegate()
	 */
	protected function getMgrDelegate($notificationType, $assocType, $assocId) {
		switch ($notificationType) {
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.EditorAssignmentNotificationManager');
				return new EditorAssignmentNotificationManager($notificationType);
			case NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.EditorDecisionNotificationManager');
				return new EditorDecisionNotificationManager($notificationType);
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.ApproveSubmissionNotificationManager');
				return new ApproveSubmissionNotificationManager($notificationType);
		}
		// Otherwise, fall back on parent class
		return parent::getMgrDelegate($notificationType, $assocType, $assocId);
	}
}

?>
