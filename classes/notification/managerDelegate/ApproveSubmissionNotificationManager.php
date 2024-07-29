<?php

/**
 * @file classes/notification/managerDelegate/ApproveSubmissionNotificationManager.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ApproveSubmissionNotificationManager
 *
 * @brief Notification manager delegate that handles notifications related with
 * submission approval process.
 */

namespace APP\notification\managerDelegate;

use APP\core\Application;
use PKP\core\PKPRequest;
use PKP\notification\managerDelegate\PKPApproveSubmissionNotificationManager;
use PKP\notification\Notification;

class ApproveSubmissionNotificationManager extends PKPApproveSubmissionNotificationManager
{
    /**
     * @copydoc PKPNotificationOperationManager::getNotificationUrl()
     */
    public function getNotificationUrl(PKPRequest $request, Notification $notification): string
    {
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();
        $contextDao = Application::getContextDAO();
        $context = $contextDao->getById($notification->contextId);

        return match ($notification->type) {
            Notification::NOTIFICATION_TYPE_VISIT_CATALOG => $dispatcher->url($request, Application::ROUTE_PAGE, $context->getPath(), 'manageCatalog'),
            default => parent::getNotificationUrl($request, $notification)
        };
    }

    /**
     * @copydoc PKPNotificationOperationManager::getNotificationTitle()
     */
    public function getNotificationTitle(Notification $notification): string
    {
        return match ($notification->type) {
            Notification::NOTIFICATION_TYPE_APPROVE_SUBMISSION,
            Notification::NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION => __('notification.type.approveSubmissionTitle'),
            Notification::NOTIFICATION_TYPE_VISIT_CATALOG => __('notification.type.visitCatalogTitle'),
        };
    }

    /**
     * @copydoc PKPNotificationOperationManager::getNotificationMessage()
     */
    public function getNotificationMessage(PKPRequest $request, Notification $notification): ?string
    {
        return match ($notification->type) {
            Notification::NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION => __('notification.type.formatNeedsApprovedSubmission'),
            Notification::NOTIFICATION_TYPE_VISIT_CATALOG => __('notification.type.visitCatalog'),
            Notification::NOTIFICATION_TYPE_APPROVE_SUBMISSION => __('notification.type.approveSubmission'),
            default => parent::getNotificationMessage($request, $notification)
        };
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\notification\managerDelegate\ApproveSubmissionNotificationManager', '\ApproveSubmissionNotificationManager');
}
