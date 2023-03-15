<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7725_DecisionConstantsUpdate.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7725_DecisionConstantsUpdate
 * @brief Editorial decision constant sync up across all application
 *
 * @see https://github.com/pkp/pkp-lib/issues/7725
 */

namespace APP\migration\upgrade\v3_4_0;

class I7725_DecisionConstantsUpdate extends \PKP\migration\upgrade\v3_4_0\I7725_DecisionConstantsUpdate
{
    /**
     * Get the decisions constants mappings
     *
     */
    public function getDecisionMappings(): array
    {
        return [
            // \PKP\decision\Decision::INITIAL_DECLINE
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION],
                'current_value' => 9,
                'updated_value' => 8,
            ],

            // \PKP\decision\Decision::RECOMMEND_ACCEPT
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 11,
                'updated_value' => 9,
            ],

            // \PKP\decision\Decision::RECOMMEND_PENDING_REVISIONS
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 12,
                'updated_value' => 10,
            ],

            // \PKP\decision\Decision::RECOMMEND_RESUBMIT
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 13,
                'updated_value' => 11,
            ],

            // \PKP\decision\Decision::RECOMMEND_DECLINE
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 14,
                'updated_value' => 12,
            ],

            // \PKP\decision\Decision::RECOMMEND_EXTERNAL_REVIEW
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 15,
                'updated_value' => 13,
            ],

            // \PKP\decision\Decision::NEW_EXTERNAL_ROUND
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 16,
                'updated_value' => 14,
            ],

            // \PKP\decision\Decision::REVERT_DECLINE
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 17,
                'updated_value' => 15,
            ],

            // \PKP\decision\Decision::REVERT_INITIAL_DECLINE
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION],
                'current_value' => 18,
                'updated_value' => 16,
            ],

            // \PKP\decision\Decision::SKIP_EXTERNAL_REVIEW
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EDITING],
                'current_value' => 19,
                'updated_value' => 17,
            ],

            // \PKP\decision\Decision::SKIP_INTERNAL_REVIEW
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 20,
                'updated_value' => 18,
            ],

            // \PKP\decision\Decision::ACCEPT_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EDITING],
                'current_value' => 21,
                'updated_value' => 19,
            ],

            // \PKP\decision\Decision::PENDING_REVISIONS_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 22,
                'updated_value' => 20
            ],

            // \PKP\decision\Decision::RESUBMIT_INTERNAL
            [
                'stage_id' => [],
                'current_value' => 23,
                'updated_value' => 21,
            ],

            // \PKP\decision\Decision::DECLINE_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 24,
                'updated_value' => 22,
            ],

            // \PKP\decision\Decision::RECOMMEND_ACCEPT_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 25,
                'updated_value' => 23,
            ],

            // \PKP\decision\Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 26,
                'updated_value' => 24,
            ],

            // \PKP\decision\Decision::RECOMMEND_RESUBMIT_INTERNAL
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 27,
                'updated_value' => 25,
            ],

            // \PKP\decision\Decision::RECOMMEND_DECLINE_INTERNAL
            [
                'stage_id' => [],
                'current_value' => 28,
                'updated_value' => 26,
            ],

            // \PKP\decision\Decision::REVERT_INTERNAL_DECLINE
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 29,
                'updated_value' => 27,
            ],

            // \PKP\decision\Decision::NEW_INTERNAL_ROUND
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 30,
                'updated_value' => 28,
            ],

            // \PKP\decision\Decision::BACK_FROM_PRODUCTION
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EDITING],
                'current_value' => 31,
                'updated_value' => 29,
            ],

            // \PKP\decision\Decision::BACK_FROM_COPYEDITING
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION, WORKFLOW_STAGE_ID_INTERNAL_REVIEW, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 32,
                'updated_value' => 30,
            ],

            // \PKP\decision\Decision::CANCEL_REVIEW_ROUND
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION, WORKFLOW_STAGE_ID_INTERNAL_REVIEW, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                'current_value' => 33,
                'updated_value' => 31,
            ],

            // \PKP\decision\Decision::CANCEL_INTERNAL_REVIEW_ROUND
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION, WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 34,
                'updated_value' => 32,
            ],
        ];
    }
}
