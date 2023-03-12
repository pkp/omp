<?php

/**
 * @file classes/notification/NotificationManager.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PKPNotificationManager
 * @ingroup notification
 *
 * @see NotificationDAO
 * @see Notification
 * @brief Class for Notification Manager.
 */

namespace APP\notification;

use APP\core\Application;
use APP\decision\Decision;
use APP\notification\managerDelegate\ApproveSubmissionNotificationManager;
use PKP\notification\managerDelegate\EditorAssignmentNotificationManager;
use PKP\notification\managerDelegate\EditorDecisionNotificationManager;
use PKP\notification\managerDelegate\PendingRevisionsNotificationManager;

use PKP\notification\PKPNotificationManager;

class NotificationManager extends PKPNotificationManager
{
    //
    // Public methods.
    //
    /**
     * @copydoc PKPNotificationManager::getNotificationTitle()
     */
    public function getNotificationTitle($notification)
    {
        switch ($notification->getType()) {
            case Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
                return __('notification.type.configurePaymentMethod.title');
        }
        return parent::getNotificationTitle($notification);
    }

    /**
     * @copydoc PKPNotificationManager::getIconClass()
     */
    public function getIconClass($notification)
    {
        switch ($notification->getType()) {
            case Notification::NOTIFICATION_TYPE_REVIEWER_COMMENT:
                return 'notifyIconNewComment';
        }
        return parent::getIconClass($notification);
    }

    /**
     * @copydoc PKPNotificationManager::getStyleClass()
     */
    public function getStyleClass($notification)
    {
        switch ($notification->getType()) {
            case Notification::NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
            case Notification::NOTIFICATION_TYPE_INDEX_ASSIGNMENT:
            case Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
                return NOTIFICATION_STYLE_CLASS_WARNING;
        }
        return parent::getStyleClass($notification);
    }

    /**
     * @copydoc PKPNotificationManager::isVisibleToAllUsers()
     */
    public function isVisibleToAllUsers($notificationType, $assocType, $assocId)
    {
        switch ($notificationType) {
            case Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
                return true;
            default:
                return parent::isVisibleToAllUsers($notificationType, $assocType, $assocId);
        }
    }

    /**
     * @copydoc PKPNotificationManager::getMgrDelegate()
     */
    protected function getMgrDelegate($notificationType, $assocType, $assocId)
    {
        switch ($notificationType) {
            case Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
                assert($assocType == Application::ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
                return new EditorAssignmentNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW:
                assert($assocType == Application::ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
                return new EditorDecisionNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
                assert($assocType == Application::ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
                return new PendingRevisionsNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_APPROVE_SUBMISSION:
            case Notification::NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
            case Notification::NOTIFICATION_TYPE_VISIT_CATALOG:
                assert($assocType == Application::ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
                return new ApproveSubmissionNotificationManager($notificationType);
        }
        // Otherwise, fall back on parent class
        return parent::getMgrDelegate($notificationType, $assocType, $assocId);
    }

    public function getNotificationTypeByEditorDecision(Decision $decision): ?int
    {
        switch ($decision->getData('decision')) {
            case Decision::INTERNAL_REVIEW:
                return Notification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW;
            case Decision::PENDING_REVISIONS_INTERNAL:
                return Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS;
            default:
                return parent::getNotificationTypeByEditorDecision($decision);
        }
        return null;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\notification\NotificationManager', '\NotificationManager');
}
