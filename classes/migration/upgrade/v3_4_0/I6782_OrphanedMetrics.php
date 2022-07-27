<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I6782_OrphanedMetrics.inc.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I6782_OrphanedMetrics
 * @brief Migrate usage stats settings, and data from the old DB table metrics into the new DB tables.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class I6782_OrphanedMetrics extends \PKP\migration\upgrade\v3_4_0\I6782_OrphanedMetrics
{
    private const ASSOC_TYPE_CONTEXT = 0x0000200;
    private const ASSOC_TYPE_SERIES = 0x0000212;

    protected function getContextAssocType(): int
    {
        return self::ASSOC_TYPE_CONTEXT;
    }

    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextKeyField(): string
    {
        return 'press_id';
    }

    protected function getRepresentationTable(): string
    {
        return 'publication_formats';
    }

    protected function getRepresentationKeyField(): string
    {
        return 'publication_format_id';
    }

    /**
     * Run the migration.
     */
    public function up(): void
    {
        parent::up();

        $metricsColumns = Schema::getColumnListing('metrics_tmp');

        // Clean orphaned series IDs
        // as assoc_id
        $orphanedIds = DB::table('metrics AS m')->leftJoin('series AS s', 'm.assoc_id', '=', 's.series_id')->where('m.assoc_type', '=', self::ASSOC_TYPE_SERIES)->whereNull('s.series_id')->distinct()->pluck('m.assoc_id');
        $orphandedSeries = DB::table('metrics')->select($metricsColumns)->where('assoc_type', '=', self::ASSOC_TYPE_SERIES)->whereIn('assoc_id', $orphanedIds);
        DB::table('metrics_tmp')->insertUsing($metricsColumns, $orphandedSeries);
        DB::table('metrics')->where('assoc_type', '=', self::ASSOC_TYPE_SERIES)->whereIn('assoc_id', $orphanedIds)->delete();
    }
}
