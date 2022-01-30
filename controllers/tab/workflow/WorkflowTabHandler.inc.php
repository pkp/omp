<?php

/**
 * @file controllers/tab/workflow/WorkflowTabHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WorkflowTabHandler
 * @ingroup controllers_tab_workflow
 *
 * @brief Handle AJAX operations for workflow tabs.
 */

// Import the base Handler.
import('lib.pkp.controllers.tab.workflow.PKPWorkflowTabHandler');

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
            NOTIFICATION_LEVEL_NORMAL => [
                NOTIFICATION_TYPE_VISIT_CATALOG => [ASSOC_TYPE_SUBMISSION, $submissionId],
                NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION => [ASSOC_TYPE_MONOGRAPH, $submissionId],
            ],
            NOTIFICATION_LEVEL_TRIVIAL => []
        ];
    }
}
