<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7265_EditorialDecisions.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7265_EditorialDecisions
 *
 * @brief Database migrations for editorial decision refactor.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;

class I7265_EditorialDecisions extends \PKP\migration\upgrade\v3_4_0\I7265_EditorialDecisions
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        parent::up();
        $this->upNewDecisions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        parent::down();
        $this->downNewDecisions();
    }

    /**
     * Migrate to the new decision classes
     *
     * The same decision may have been taken in more than one stage
     * before. For example, SUBMISSION_EDITOR_DECISION_ACCEPT may
     * have been taken in the submission, internal review, or external
     * review stage. Now, this is represented by three decisions:
     *
     * APP\decision\Decision::ACCEPT
     * APP\decision\Decision::SKIP_EXTERNAL_REVIEW
     * APP\decision\Decision::ACCEPT_INTERNAL
     *
     * This migration maps all of the old decisions to the new ones.
     */
    public function upNewDecisions()
    {
        foreach ($this->getDecisionMap() as $oldDecision => $changes) {
            foreach ($changes as $stageId => $newDecision) {
                DB::table('edit_decisions')
                    ->where('stage_id', '=', $stageId)
                    ->where('decision', '=', $oldDecision)
                    ->update(['decision' => $newDecision]);
            }
        }
    }

    /**
     * Reverse the decision type changes
     *
     * @see self::upNewSubmissionDecisions()
     */
    public function downNewDecisions()
    {
        foreach ($this->getDecisionMap() as $oldDecision => $changes) {
            foreach ($changes as $stageId => $newDecision) {
                DB::table('edit_decisions')
                    ->where('stage_id', '=', $stageId)
                    ->where('decision', '=', $newDecision)
                    ->update(['decision' => $oldDecision]);
            }
        }
    }

    /**
     * Get a map of the decisions in the following format
     *
     * [
     *  $oldDecisionA => [
     *      $stageId1 => $newDecision1,
     *      $stageId2 => $newDecision2,
     *  ],
     *  $oldDecisionB => [
     *      $stageId1 => $newDecision1,
     *  ],
     * ]
     *
     * Only decisions that need to be changed are listed.
     */
    protected function getDecisionMap(): array
    {
        return [
            // Change Decision::ACCEPT...
            2 => [
                // ...in WORKFLOW_STAGE_ID_SUBMISSION to Decision::SKIP_EXTERNAL_REVIEW
                1 => 19,
                // ...in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::ACCEPT_INTERNAL
                2 => 24,
                // Accept decisions in WORKFLOW_STAGE_ID_EXTERNAL_REVIEW are not changed.
            ],

            // Change Decision::EXTERNAL_REVIEW...
            3 => [
                // ...in WORKFLOW_STAGE_ID_SUBMISSION to Decision::SKIP_INTERNAL_REVIEW
                1 => 23,
                // Send to external review decisions in WORKFLOW_STAGE_ID_INTERNAL_REVIEW are not changed.
            ],

            // Change Decision::DECLINE...
            6 => [
                // ...in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::DECLINE_INTERNAL
                2 => 27,
            ],

            // Change Decision::RECOMMEND_ACCEPT...
            11 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::RECOMMEND_ACCEPT_INTERNAL
                2 => 28,
            ],

            // Change Decision::RECOMMEND_DECLINE...
            14 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::RECOMMEND_DECLINE_INTERNAL
                2 => 31,
            ],

            // Change Decision::RECOMMEND_RESUBMIT...
            13 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::RECOMMEND_RESUBMIT_INTERNAL
                2 => 30,
            ],

            // Change Decision::RECOMMEND_PENDING_REVISIONS...
            12 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL
                2 => 29,
            ],

            // Change Decision::PENDING_REVISIONS...
            4 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::PENDING_REVISIONS_INTERNAL
                2 => 25,
            ],

            // Change Decision::RESUBMIT...
            5 => [
                // ... in WORKFLOW_STAGE_ID_INTERNAL_REVIEW to Decision::RESUBMIT_INTERNAL
                2 => 26,
            ],
        ];
    }

    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    protected function getContextIdColumn(): string
    {
        return 'press_id';
    }
}
