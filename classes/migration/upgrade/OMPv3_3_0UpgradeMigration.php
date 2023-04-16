<?php

/**
 * @file classes/migration/upgrade/OMPv3_3_0UpgradeMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPv3_3_0UpgradeMigration
 *
 * @brief Describe database table structures.
 */

namespace APP\migration\upgrade;

use Illuminate\Support\Facades\DB;

class OMPv3_3_0UpgradeMigration extends \PKP\migration\upgrade\PKPv3_3_0UpgradeMigration
{
    private const SUBMISSION_FILE_REVIEW_FILE = 4; //self::SUBMISSION_FILE_REVIEW_FILE;
    private const SUBMISSION_FILE_INTERNAL_REVIEW_FILE = 19; //self::SUBMISSION_FILE_INTERNAL_REVIEW_FILE;
    private const SUBMISSION_FILE_REVIEW_REVISION = 15; //self::SUBMISSION_FILE_REVIEW_REVISION;
    private const SUBMISSION_FILE_INTERNAL_REVIEW_REVISION = 20; //self::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION;
    private const WORKFLOW_STAGE_ID_INTERNAL_REVIEW = 2; //PKPApplication::WORKFLOW_STAGE_ID_INTERNAL_REVIEW

    protected function getSubmissionPath(): string
    {
        return 'monographs';
    }

    protected function getContextPath(): string
    {
        return 'presses';
    }

    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextKeyField(): string
    {
        return 'press_id';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    protected function getSectionTable(): string
    {
        return 'series';
    }

    protected function getSerializedSettings(): array
    {
        return [
            'site_settings' => [
                'enableBulkEmails',
                'installedLocales',
                'pageHeaderTitleImage',
                'sidebar',
                'styleSheet',
                'supportedLocales',
            ],
            'press_settings' => [
                'disableBulkEmailUserGroups',
                'favicon',
                'homepageImage',
                'pageHeaderLogoImage',
                'sidebar',
                'styleSheet',
                'submissionChecklist',
                'supportedFormLocales',
                'supportedLocales',
                'supportedSubmissionLocales',
                'enablePublisherId',
                'pressThumbnail',
            ],
            'publication_settings' => [
                'categoryIds',
                'coverImage',
                'disciplines',
                'keywords',
                'languages',
                'subjects',
                'supportingAgencies',
            ]
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        parent::up();

        // Delete the old MODS34 filters
        DB::statement("DELETE FROM filters WHERE class_name='plugins.metadata.mods34.filter.Mods34SchemaMonographAdapter'");
        DB::statement("DELETE FROM filter_groups WHERE symbolic IN ('monograph=>mods34', 'mods34=>monograph')");

        // pkp/pkp-lib#6604 ONIX filters still refer to Monograph rather than Submission
        DB::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission' WHERE input_type = 'class::classes.monograph.Monograph';");
        DB::statement("UPDATE filter_groups SET output_type = 'class::classes.submission.Submission[]' WHERE input_type = 'class::classes.monograph.Monograph[]';");

        // pkp/pkp-lib#6609 ONIX filters does not take array of submissions as input
        DB::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission[]' WHERE symbolic = 'monograph=>onix30-xml';");
    }

    /**
     * Complete specific submission file migrations
     *
     * The main submission file migration is done in
     * PKPv3_3_0UpgradeMigration and that migration must
     * be run before this one.
     */
    protected function migrateSubmissionFiles()
    {
        parent::migrateSubmissionFiles();

        // Update file stage for all internal review files
        DB::table('submission_files as sf')
            ->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
            ->where('sf.file_stage', '=', self::SUBMISSION_FILE_REVIEW_FILE)
            ->where('rrf.stage_id', '=', self::WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
            ->update(['sf.file_stage' => self::SUBMISSION_FILE_INTERNAL_REVIEW_FILE]);
        DB::table('submission_files as sf')
            ->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
            ->where('sf.file_stage', '=', self::SUBMISSION_FILE_REVIEW_REVISION)
            ->where('rrf.stage_id', '=', self::WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
            ->update(['sf.file_stage' => self::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION]);

        // Update the fileStage property for all event logs where the
        // file has been moved to an internal review file stage
        $internalStageIds = [
            self::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
            self::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
        ];
        foreach ($internalStageIds as $internalStageId) {
            $submissionIds = DB::table('submission_files')
                ->where('file_stage', '=', $internalStageId)
                ->pluck('submission_file_id');
            $logIdsToChange = DB::table('event_log_settings')
                ->where('setting_name', '=', 'submissionFileId')
                ->whereIn('setting_value', $submissionIds)
                ->pluck('log_id');
            DB::table('event_log_settings')
                ->whereIn('log_id', $logIdsToChange)
                ->where('setting_name', '=', 'fileStage')
                ->update(['setting_value' => $internalStageId]);
        }
    }
}
