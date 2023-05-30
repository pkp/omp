<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I6782_MetricsSeries.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I6782_MetricsSeries
 *
 * @brief Migrate series stats data from the old DB table metrics into the new DB table metrics_series.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\Migration;

class I6782_MetricsSeries extends Migration
{
    private const ASSOC_TYPE_SERIES = 0x0000212;

    /**
     * Run the migration.
     */
    public function up(): void
    {
        $dayFormatSql = "DATE_FORMAT(STR_TO_DATE(m.day, '%Y%m%d'), '%Y-%m-%d')";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $dayFormatSql = "to_date(m.day, 'YYYYMMDD')";
        }

        // The not existing foreign keys should already be moved to the metrics_tmp in I6782_OrphanedMetrics
        $selectSeriesMetrics = DB::table('metrics as m')
            ->select(DB::raw("m.load_id, m.context_id, m.assoc_id, {$dayFormatSql}, m.metric"))
            ->where('m.assoc_type', '=', self::ASSOC_TYPE_SERIES)
            ->where('m.metric_type', '=', 'omp::counter');
        DB::table('metrics_series')->insertUsing(['load_id', 'context_id', 'series_id', 'date', 'metric'], $selectSeriesMetrics);
    }

    /**
     * Reverse the downgrades
     *
     * @throws DowngradeNotSupportedException
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
