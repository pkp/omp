<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I10511_RemoveSeriesMenuItems.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I10511_RemoveSeriesMenuItems
 *
 * @brief Remove invalid series menu items.
 */

namespace APP\migration\upgrade\v3_5_0;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\Migration;

class I10511_RemoveSeriesMenuItems extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $invalidNavigationMenuItemIds = DB::table('navigation_menu_items')
            ->where('type', 'NMI_TYPE_SERIES')
            ->whereNotIn('path', function (Builder $query) {
                $query->select('series_id')->from('series');
            })->pluck('navigation_menu_item_id');

        DB::table('navigation_menu_item_assignments')
            ->whereIn('parent_id', $invalidNavigationMenuItemIds)
            ->update(['parent_id' => null]);

        DB::table('navigation_menu_items')
            ->where('type', 'NMI_TYPE_SERIES')
            ->whereIn('navigation_menu_item_id', $invalidNavigationMenuItemIds)
            ->delete();
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
