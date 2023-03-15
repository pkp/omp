<?php

/**
 * @file classes/migration/upgrade/v3_4_0/PreflightCheckMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PreflightCheckMigration
 * @brief Check for common problems early in the upgrade process.
 */

namespace APP\migration\upgrade\v3_4_0;

use Exception;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;

class PreflightCheckMigration extends \PKP\migration\upgrade\v3_4_0\PreflightCheckMigration
{
    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    protected function getContextKeyField(): string
    {
        return 'press_id';
    }

    public function up(): void
    {
        parent::up();
        try {
            // Clean orphaned series entries by press_id
            $orphanedIds = DB::table('series AS s')->leftJoin('presses AS p', 's.press_id', '=', 'p.press_id')->whereNull('p.press_id')->distinct()->pluck('s.press_id');
            foreach ($orphanedIds as $pressId) {
                DB::table('series')->where('press_id', '=', $pressId)->delete();
            }

            // Clean orphaned sections entries by review_form_id
            $orphanedIds = DB::table('series AS s')->leftJoin('review_forms AS rf', 's.review_form_id', '=', 'rf.review_form_id')->whereNull('rf.review_form_id')->whereNotNull('s.review_form_id')->distinct()->pluck('s.review_form_id');
            foreach ($orphanedIds as $reviewFormId) {
                DB::table('series')->where('review_form_id', '=', $reviewFormId)->update(['review_form_id' => null]);
            }

            // Clean orphaned section_settings entries
            $orphanedIds = DB::table('series_settings AS ss')->leftJoin('series AS s', 'ss.series_id', '=', 's.series_id')->whereNull('s.series_id')->distinct()->pluck('ss.series_id');
            foreach ($orphanedIds as $seriesId) {
                DB::table('series_settings')->where('series_id', '=', $seriesId)->delete();
            }

            // Clean orphaned publications entries by primary_contact_id
            switch (true) {
                case DB::connection() instanceof MySqlConnection:
                    DB::statement('UPDATE publications p LEFT JOIN users u ON (p.primary_contact_id = u.user_id) SET p.primary_contact_id = NULL WHERE u.user_id IS NULL');
                    break;
                case DB::connection() instanceof PostgresConnection:
                    DB::statement('UPDATE publications SET primary_contact_id = NULL WHERE publication_id IN (SELECT publication_id FROM publications p LEFT JOIN users u ON (p.primary_contact_id = u.user_id) WHERE u.user_id IS NULL AND p.primary_contact_id IS NOT NULL)');
                    break;
                default: throw new Exception('Unknown database connection type!');
            }

            // Clean orphaned publication_formats entries by publication_id
            $orphanedIds = DB::table('publication_formats AS pf')->leftJoin('publications AS p', 'pf.publication_id', '=', 'p.publication_id')->whereNull('p.publication_id')->distinct()->pluck('pf.publication_id');
            foreach ($orphanedIds as $publicationId) {
                DB::table('publication_formats')->where('publication_id', '=', $publicationId)->delete();
            }

            // Clean orphaned publication_format_settings entries
            $orphanedIds = DB::table('publication_format_settings AS pfs')->leftJoin('publication_formats AS pf', 'pfs.publication_format_id', '=', 'pf.publication_format_id')->whereNull('pf.publication_format_id')->distinct()->pluck('pfs.publication_format_id');
            foreach ($orphanedIds as $publicationFormatId) {
                DB::table('publication_format_settings')->where('publication_format_id', '=', $publicationFormatId)->delete();
            }

            // Clean orphaned completed_payments entries by context_id
            $orphanedIds = DB::table('completed_payments AS cp')->leftJoin('presses AS p', 'p.press_id', '=', 'cp.context_id')->whereNull('p.press_id')->distinct()->pluck('cp.context_id');
            foreach ($orphanedIds as $pressId) {
                DB::table('completed_payments')->where('context_id', '=', $pressId)->delete();
            }

            // Clean orphaned completed_payments entries by user_id
            $orphanedIds = DB::table('completed_payments AS cp')->leftJoin('users AS u', 'u.user_id', '=', 'cp.user_id')->whereNull('u.user_id')->distinct()->pluck('cp.user_id');
            foreach ($orphanedIds as $userId) {
                DB::table('completed_payments')->where('user_id', '=', $userId)->delete();
            }
        } catch (\Exception $e) {
            if ($fallbackVersion = $this->setFallbackVersion()) {
                $this->_installer->log("A pre-flight check failed. The software was successfully upgraded to {$fallbackVersion} but could not be upgraded further (to " . $this->_installer->newVersion->getVersionString() . '). Check and correct the error, then try again.');
            }
            throw $e;
        }
    }
}
