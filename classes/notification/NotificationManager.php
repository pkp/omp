<?php

/**
 * @file classes/notification/NotificationManager.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NotificationManager
 *
 * @see Notification
 *
 * @brief Class for Notification Manager.
 */

namespace APP\notification;

use APP\core\Application;
use APP\decision\Decision;
use APP\notification\managerDelegate\ApproveSubmissionNotificationManager;
use PKP\notification\managerDelegate\EditorAssignmentNotificationManager;
use PKP\notification\managerDelegate\EditorDecisionNotificationManager;
use PKP\notification\managerDelegate\PendingRevisionsNotificationManager;
use PKP\notification\Notification;
use PKP\notification\NotificationManagerDelegate;
use PKP\notification\PKPNotificationManager;

class NotificationManager extends PKPNotificationManager
{
    //
    // Public methods.
    //
    /**
     * @copydoc PKPNotificationManager::getNotificationTitle()
     */
    public function getNotificationTitle(Notification $notification): string
    {
        return match($notification->type) {
            Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD => __('notification.type.configurePaymentMethod.title'),
            default => parent::getNotificationTitle($notification)
        };
    }

    /**
     * @copydoc PKPNotificationManager::getIconClass()
     */
    public function getIconClass(Notification $notification): string
    {
        return match ($notification->type) {
            Notification::NOTIFICATION_TYPE_REVIEWER_COMMENT => 'notifyIconNewComment',
            default => parent::getIconClass($notification)
        };
    }

    /**
     * @copydoc PKPNotificationManager::getStyleClass()
     */
    public function getStyleClass(Notification $notification): string
    {
        return match($notification->type) {
            Notification::NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT,
            Notification::NOTIFICATION_TYPE_INDEX_ASSIGNMENT,
            Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD => NOTIFICATION_STYLE_CLASS_WARNING,
            default => parent::getStyleClass($notification)
        };
    }

    /**
     * @copydoc PKPNotificationManager::isVisibleToAllUsers()
     */
    public function isVisibleToAllUsers(int $notificationType, int $assocType, int $assocId): bool
    {
        return match ($notificationType) {
            Notification::NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD => true,
            default => parent::isVisibleToAllUsers($notificationType, $assocType, $assocId)
        };
    }

    /**
     * @copydoc PKPNotificationManager::getMgrDelegate()
     */
    protected function getMgrDelegate(int $notificationType, ?int $assocType, ?int $assocId): ?NotificationManagerDelegate
    {
        switch ($notificationType) {
            case Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
                if ($assocType != Application::ASSOC_TYPE_SUBMISSION) {
                    throw new \Exception('Unexpected assoc type!');
                }
                return new EditorAssignmentNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW:
                if ($assocType != Application::ASSOC_TYPE_SUBMISSION) {
                    throw new \Exception('Unexpected assoc type!');
                }
                return new EditorDecisionNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
                if ($assocType != Application::ASSOC_TYPE_SUBMISSION) {
                    throw new \Exception('Unexpected assoc type!');
                }
                return new PendingRevisionsNotificationManager($notificationType);
            case Notification::NOTIFICATION_TYPE_APPROVE_SUBMISSION:
            case Notification::NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
            case Notification::NOTIFICATION_TYPE_VISIT_CATALOG:
                if ($assocType != Application::ASSOC_TYPE_SUBMISSION) {
                    throw new \Exception('Unexpected assoc type!');
                }
                return new ApproveSubmissionNotificationManager($notificationType);
        }
        // Otherwise, fall back on parent class
        return parent::getMgrDelegate($notificationType, $assocType, $assocId);
    }

    public function getNotificationTypeByEditorDecision(Decision $decision): ?int
    {
        return match ($decision->getData('decision')) {
            Decision::INTERNAL_REVIEW => Notification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW,
            Decision::PENDING_REVISIONS_INTERNAL => Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS,
            default => parent::getNotificationTypeByEditorDecision($decision)
        };
    }
}
