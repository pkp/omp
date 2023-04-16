<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7434_WorkType.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7434_WorkType
 *
 * @brief Consolidate submission workType settings in the submissions table
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;
use PKP\config\Config;

class I7434_WorkType extends \PKP\migration\Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement(
                "UPDATE submissions SET work_type = CAST(ss.setting_value AS INTEGER) FROM submission_settings AS ss
                WHERE ss.submission_id = submissions.submission_id
                AND ss.setting_name = 'workType'"
            );
        } else {
            DB::statement(
                "UPDATE submissions as s, submission_settings as ss
                SET s.work_type = CAST(ss.setting_value as UNSIGNED)
                WHERE ss.submission_id = s.submission_id
                    AND ss.setting_name = 'workType'"
            );
        }

        DB::table('submission_settings')
            ->where('setting_name', '=', 'workType')
            ->delete();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Data should not be returned to the settings table
    }
}
