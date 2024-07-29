<?php

/**
 * @file controllers/tab/workflow/WorkflowTabHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WorkflowTabHandler
 *
 * @ingroup controllers_tab_workflow
 *
 * @brief Handle AJAX operations for workflow tabs.
 */

namespace APP\controllers\tab\workflow;

use APP\core\Application;
use APP\decision\types\NewInternalReviewRound;
use PKP\controllers\tab\workflow\PKPWorkflowTabHandler;
use PKP\decision\DecisionType;
use PKP\decision\types\NewExternalReviewRound;
use PKP\notification\Notification;

class WorkflowTabHandler extends PKPWorkflowTabHandler
{
    /**
     * Get all production notification options to be used in the production stage tab.
     *
     * @param int $submissionId
     *
     * @return array
     */
    protected function getProductionNotificationOptions($submissionId)
    {
        return [
            Notification::NOTIFICATION_LEVEL_NORMAL => [
                Notification::NOTIFICATION_TYPE_VISIT_CATALOG => [Application::ASSOC_TYPE_SUBMISSION, $submissionId],
                Notification::NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION => [Application::ASSOC_TYPE_MONOGRAPH, $submissionId],
            ],
            Notification::NOTIFICATION_LEVEL_TRIVIAL => []
        ];
    }

    protected function getNewReviewRoundDecisionType(int $stageId): DecisionType
    {
        if ($stageId === WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
            return new NewInternalReviewRound();
        }
        return new NewExternalReviewRound();
    }
}
