<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7725_DecisionConstantsUpdate.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7725_DecisionConstantsUpdate
 *
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
        // stage_id filtering removed: all old OMP 3.3 decision values (after I7265
        // stage-splitting) are unique, and the parent class's updated_at tracking
        // mechanism prevents collisions between sequential mappings (e.g., 15→13
        // then 13→11). OMP 3.3 had no validation on which decisions could be
        // recorded at which stages, so decisions can exist at any stage in legacy data.
        // See https://github.com/pkp/pkp-lib/issues/12357
        return [
            ['current_value' => 9,  'updated_value' => 8],   // INITIAL_DECLINE
            ['current_value' => 11, 'updated_value' => 9],   // RECOMMEND_ACCEPT
            ['current_value' => 12, 'updated_value' => 10],  // RECOMMEND_PENDING_REVISIONS
            ['current_value' => 13, 'updated_value' => 11],  // RECOMMEND_RESUBMIT
            ['current_value' => 14, 'updated_value' => 12],  // RECOMMEND_DECLINE
            ['current_value' => 15, 'updated_value' => 13],  // RECOMMEND_EXTERNAL_REVIEW
            ['current_value' => 16, 'updated_value' => 14],  // NEW_EXTERNAL_ROUND
            ['current_value' => 17, 'updated_value' => 15],  // REVERT_DECLINE
            ['current_value' => 18, 'updated_value' => 16],  // REVERT_INITIAL_DECLINE
            ['current_value' => 19, 'updated_value' => 17],  // SKIP_EXTERNAL_REVIEW
            ['current_value' => 20, 'updated_value' => 18],  // SKIP_INTERNAL_REVIEW
            ['current_value' => 21, 'updated_value' => 19],  // ACCEPT_INTERNAL
            ['current_value' => 22, 'updated_value' => 20],  // PENDING_REVISIONS_INTERNAL
            ['current_value' => 23, 'updated_value' => 21],  // RESUBMIT_INTERNAL
            ['current_value' => 24, 'updated_value' => 22],  // DECLINE_INTERNAL
            ['current_value' => 25, 'updated_value' => 23],  // RECOMMEND_ACCEPT_INTERNAL
            ['current_value' => 26, 'updated_value' => 24],  // RECOMMEND_PENDING_REVISIONS_INTERNAL
            ['current_value' => 27, 'updated_value' => 25],  // RECOMMEND_RESUBMIT_INTERNAL
            ['current_value' => 28, 'updated_value' => 26],  // RECOMMEND_DECLINE_INTERNAL
            ['current_value' => 29, 'updated_value' => 27],  // REVERT_INTERNAL_DECLINE
            ['current_value' => 30, 'updated_value' => 28],  // NEW_INTERNAL_ROUND
            ['current_value' => 31, 'updated_value' => 29],  // BACK_FROM_PRODUCTION
            ['current_value' => 32, 'updated_value' => 30],  // BACK_FROM_COPYEDITING
            ['current_value' => 33, 'updated_value' => 31],  // CANCEL_REVIEW_ROUND
            ['current_value' => 34, 'updated_value' => 32],  // CANCEL_INTERNAL_REVIEW_ROUND
        ];
    }
}
