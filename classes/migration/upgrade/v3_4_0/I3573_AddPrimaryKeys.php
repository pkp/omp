<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I3573_AddPrimaryKeys.php.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2000-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I3573_AddPrimaryKeys.php
 *
 * @brief Add primary keys to tables that do not have them, to better support database replication.
 *
 */

namespace APP\migration\upgrade\v3_4_0;

class I3573_AddPrimaryKeys extends \PKP\migration\upgrade\v3_4_0\I3573_AddPrimaryKeys
{
    public static function getKeyNames(): array
    {
        return array_merge(parent::getKeyNames(), [
            'publication_format_settings' => 'publication_format_setting_id',
            'features' => 'feature_id', // INDEX RENAME?
            'new_releases' => 'new_release_id',
            'spotlight_settings' => 'spotlight_setting_id',
            'submission_chapter_settings' => 'submission_chapter_setting_id',
            'series_settings' => 'series_setting_id',
            'press_settings' => 'press_setting_id',
            'metrics_context' => 'metrics_context_id',
            'metrics_series' => 'metrics_series_id',
            'metrics_submission' => 'metrics_submission_id',
            'metrics_counter_submission_daily' => 'metrics_counter_submission_daily_id',
            'metrics_counter_submission_monthly' => 'metrics_counter_submission_monthly_id',
            'metrics_counter_submission_institution_daily' => 'metrics_counter_submission_institution_daily_id',
            'metrics_counter_submission_institution_monthly' => 'metrics_counter_submission_institution_monthly_id',
            'metrics_submission_geo_daily' => 'metrics_submission_geo_daily_id',
            'metrics_submission_geo_monthly' => 'metrics_submission_geo_monthly_id',
            'usage_stats_total_temporary_records' => 'usage_stats_temp_total_id',
            'usage_stats_unique_item_investigations_temporary_records' => 'usage_stats_temp_unique_item_id',
            'usage_stats_unique_item_requests_temporary_records' => 'usage_stats_temp_item_id',
            'usage_stats_unique_title_investigations_temporary_records' => 'usage_stats_temp_unique_investigations_id',
            'usage_stats_unique_title_requests_temporary_records' => 'usage_stats_temp_unique_requests_id',
            'usage_stats_institution_temporary_records' => 'usage_stats_temp_institution_id',
        ]);
    }

    public static function getIndexData(): array
    {
        return array_merge(parent::getIndexData(), [
            'press_settings' => ['press_settings_pkey', ['press_id', 'locale', 'setting_name'], 'press_settings_unique', true],
            'series_settings' => ['series_settings_pkey', ['series_id', 'locale', 'setting_name'], 'series_settings_unique', true],
            'publication_format_settings' => ['publication_format_settings_pkey', ['publication_format_id', 'locale', 'setting_name'], 'publication_format_settings_unique', true],
            'submission_chapter_settings' => ['submission_chapter_settings_pkey', ['chapter_id', 'locale', 'setting_name'], 'submission_chapter_settings_unique', true],
            'features' => ['press_features_pkey', ['assoc_type', 'assoc_id', 'submission_id'], 'press_features_unique', true],
            'new_releases' => ['new_releases_pkey', ['assoc_type', 'assoc_id', 'submission_id'], 'new_releases_unique', true],
            'spotlight_settings' => ['spotlight_settings_pkey', ['spotlight_id', 'locale', 'setting_name'], 'spotlight_settings_unique', true],
        ]);
    }
}
