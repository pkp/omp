<?php

/**
 * @file classes/migration/upgrade/v3_4_0/MergeLocalesMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MergeLocalesMigration
 *
 * @brief Change Locales from locale_countryCode localization folder notation to locale localization folder notation
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Collection;

class MergeLocalesMigration extends \PKP\migration\upgrade\v3_4_0\MergeLocalesMigration
{
    protected string $CONTEXT_TABLE = 'presses';
    protected string $CONTEXT_SETTINGS_TABLE = 'press_settings';
    protected string $CONTEXT_COLUMN = 'press_id';

    public static function getSettingsTables(): Collection
    {
        return collect([
            'press_settings' => ['press_id', 'press_setting_id'],
            'publication_format_settings' => ['publication_format_id', 'publication_format_setting_id'],
            'series_settings' => ['series_id', 'series_setting_id'],
            'spotlight_settings' => ['spotlight_id', 'spotlight_setting_id'],
            'submission_chapter_settings' => ['chapter_id', 'submission_chapter_setting_id'],
        ])->merge(parent::getSettingsTables());
    }
}
