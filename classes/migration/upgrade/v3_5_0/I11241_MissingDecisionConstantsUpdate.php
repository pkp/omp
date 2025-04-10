<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I11241_MissingDecisionConstantsUpdate.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I11241_MissingDecisionConstantsUpdate
 *
 * @brief Fixed the missing decisions data in stages
 *
 * @see https://github.com/pkp/pkp-lib/issues/11241
 */

namespace APP\migration\upgrade\v3_5_0;

class I11241_MissingDecisionConstantsUpdate extends \PKP\migration\upgrade\v3_4_0\I7725_DecisionConstantsUpdate
{
    /**
     * Get the decisions constants mappings
     */
    public function getDecisionMappings(): array
    {
        return [
            // \PKP\decision\Decision::SKIP_EXTERNAL_REVIEW
            [
                'stage_id' => [WORKFLOW_STAGE_ID_SUBMISSION],
                'current_value' => 19,
                'updated_value' => 17,
            ],

            // \PKP\decision\Decision::BACK_FROM_PRODUCTION
            [
                'stage_id' => [WORKFLOW_STAGE_ID_PRODUCTION],
                'current_value' => 31,
                'updated_value' => 29,
            ],

            // \PKP\decision\Decision::BACK_FROM_COPYEDITING
            [
                'stage_id' => [WORKFLOW_STAGE_ID_EDITING],
                'current_value' => 32,
                'updated_value' => 30,
            ],
        ];
    }
}
