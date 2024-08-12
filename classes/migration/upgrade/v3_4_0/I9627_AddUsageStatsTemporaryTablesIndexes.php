<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I9627_AddUsageStatsTemporaryTablesIndexes.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I9627_AddUsageStatsTemporaryTablesIndexes
 *
 * @brief Add an index to temporary usage stats tables to fix/improve the removeDoubleClicks and compileUniqueClicks query.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;

class I9627_AddUsageStatsTemporaryTablesIndexes extends \PKP\migration\upgrade\v3_4_0\I9627_AddUsageStatsTemporaryTablesIndexes
{
    public $ipTables = [
        'usage_stats_total_temporary_records',
        'usage_stats_unique_item_investigations_temporary_records',
        'usage_stats_unique_item_requests_temporary_records',
        'usage_stats_unique_title_investigations_temporary_records',
        'usage_stats_unique_title_requests_temporary_records'
    ];

    /**
     * Run the migration.
     */
    public function up(): void
    {
        parent::up();

        Schema::table('usage_stats_unique_title_investigations_temporary_records', function (Blueprint $table) {
            if (!Schema::hasIndex('usage_stats_unique_title_investigations_temporary_records', 'usti_load_id_context_id_ip')) {
                $table->index(['load_id', 'context_id', 'ip'], 'usti_load_id_context_id_ip');
            }
        });

        Schema::table('usage_stats_unique_title_requests_temporary_records', function (Blueprint $table) {
            if (!Schema::hasIndex('usage_stats_unique_title_investigations_temporary_records', 'ustr_load_id_context_id_ip')) {
                $table->index(['load_id', 'context_id', 'ip'], 'ustr_load_id_context_id_ip');
            }
        });
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
