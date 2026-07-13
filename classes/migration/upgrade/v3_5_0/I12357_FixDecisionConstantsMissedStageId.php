<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I12357_FixDecisionConstantsMissedStageId.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I12357_FixDecisionConstantsMissedStageId
 *
 * @brief Fix RECOMMEND_EXTERNAL_REVIEW decisions missed by previous migrations.
 *        OMP 3.3 offered RECOMMEND_EXTERNAL_REVIEW only at INTERNAL_REVIEW stage,
 *        but I7725 only mapped it at EXTERNAL_REVIEW, and I11241 did not include it.
 *
 * @see https://github.com/pkp/pkp-lib/issues/12357
 */

namespace APP\migration\upgrade\v3_5_0;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\install\DowngradeNotSupportedException;

class I12357_FixDecisionConstantsMissedStageId extends \PKP\migration\upgrade\v3_4_0\I7725_DecisionConstantsUpdate
{
    /**
     * Get the decisions constants mappings
     */
    public function getDecisionMappings(): array
    {
        // Only RECOMMEND_EXTERNAL_REVIEW (15→13) is targeted here.
        // This is collision-safe because correctly-migrated REVERT_DECLINE rows
        // (17→15) are at EXTERNAL_REVIEW stage, not INTERNAL_REVIEW.
        return [
            [
                'stage_id' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW],
                'current_value' => 15,
                'updated_value' => 13,
            ],
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If the first installed version is 3.4.0+, no pre-3.4 legacy data exists
        $firstInstalledVersion = DB::table('versions')
            ->where('product', Application::get()->getName())
            ->where('product_type', 'core')
            ->orderBy('date_installed')
            ->first();

        if ($firstInstalledVersion->major > 3 || ($firstInstalledVersion->major == 3 && $firstInstalledVersion->minor >= 4)) {
            return;
        }

        // Run the parent's up() which uses configureUpdatedAtColumn(),
        // iterates mappings with whereNull('updated_at'), and removeUpdatedAtColumn().
        // Already-migrated RECOMMEND_EXTERNAL_REVIEW rows have decision=13 (not 15),
        // so WHERE decision=15 only matches stranded rows.
        parent::up();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
