<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7128_SeriesEntityDAORefactor.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2000-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7128_SeriesEntityDAORefactor
 *
 * @brief Remove deprecated setting_type requirement after converting series DAO to use new repository pattern
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;

class I7128_SeriesEntityDAORefactor extends \PKP\migration\Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        if (Schema::hasColumn('series_settings', 'setting_type')) {
            Schema::table('series_settings', function (Blueprint $table) {
                $table->dropColumn('setting_type');
            });
        }
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
