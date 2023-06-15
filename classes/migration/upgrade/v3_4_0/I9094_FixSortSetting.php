<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I9094_FixSortSetting.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I9094_FixSortSetting
 *
 * @brief Fix sort settings for the appropriate sort order suffix -- ASC instead of 1, DESC instead of 2
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;

class I9094_FixSortSetting extends \PKP\migration\Migration
{
    public const SETTING_VALUE_MAP = [
        'title-1' => 'title-ASC',
        'title-2' => 'title-DESC',
        'datePublished-1' => 'datePublished-ASC',
        'datePublished-2' => 'datePublished-DESC',
        'seriesPosition-1' => 'seriesPosition-ASC',
        'seriesPosition-2' => 'seriesPosition-DESC',
    ];

    /**
     * Run the migration.
     */
    public function up(): void
    {
        foreach (static::SETTING_VALUE_MAP as $from => $to) {
            DB::statement('UPDATE press_settings SET setting_value=? WHERE setting_value=? AND setting_name = ?', [$to, $from, 'catalogSortOption']);
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        foreach (static::SETTING_VALUE_MAP as $from => $to) {
            DB::statement('UPDATE press_settings SET setting_value=? WHERE setting_value=? AND setting_name = ?', [$from, $to, 'catalogSortOption']);
        }
    }
}
