<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I10561_OnixFilter.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I10561_OnixFilter
 *
 * @brief Update the location of the ONIX reference file used for validation by the native import/export plugin in OMP.
 */

namespace APP\migration\upgrade\v3_5_0;

use Illuminate\Support\Facades\DB;
use PKP\migration\Migration;

class I10561_OnixFilter extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('filter_groups')
            ->where('symbolic', 'monograph=>onix30-xml')
            ->update(['output_type' => 'xml::schema(plugins/importexport/onix30/ONIX_BookProduct_3.0_reference.xsd)']);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('filter_groups')
            ->where('symbolic', 'monograph=>onix30-xml')
            ->update(['output_type' => 'xml::schema(plugins/importexport/native/ONIX_BookProduct_3.0_reference_notstrict.xsd)']);
    }
}
