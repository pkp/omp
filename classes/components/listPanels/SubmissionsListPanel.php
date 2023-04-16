<?php
/**
 * @file components/listPanels/SubmissionsListPanel.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListPanel
 *
 * @ingroup classes_components_listPanels
 *
 * @brief Instantiates and manages a UI component to list submissions.
 */

namespace APP\components\listPanels;

use PKP\components\listPanels\PKPSubmissionsListPanel;

class SubmissionsListPanel extends PKPSubmissionsListPanel
{
    /**
     * Get an array of workflow stages supported by the current app
     *
     * @return array
     */
    public function getWorkflowStages()
    {
        return [
            [
                'param' => 'stageIds',
                'value' => WORKFLOW_STAGE_ID_SUBMISSION,
                'title' => __('manager.publication.submissionStage'),
            ],
            [
                'param' => 'stageIds',
                'value' => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
                'title' => __('workflow.review.internalReview'),
            ],
            [
                'param' => 'stageIds',
                'value' => WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
                'title' => __('workflow.review.externalReview'),
            ],
            [
                'param' => 'stageIds',
                'value' => WORKFLOW_STAGE_ID_EDITING,
                'title' => __('submission.copyediting'),
            ],
            [
                'param' => 'stageIds',
                'value' => WORKFLOW_STAGE_ID_PRODUCTION,
                'title' => __('manager.publication.productionStage'),
            ],
        ];
    }
}
