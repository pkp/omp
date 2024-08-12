<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I9039_DropDeprecatedFields.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I9039_DropDeprecatedFields
 *
 * @brief Drop deprecated fields
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class I9039_DropDeprecatedFields extends \PKP\migration\upgrade\v3_4_0\I9039_DropDeprecatedFields
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        parent::up();

        // Release the index assigned to the column before dropping it
        if (Schema::hasIndex('publication_formats', 'publication_format_submission_id')) {
            Schema::table('publication_formats', fn (Blueprint $table) => $table->dropIndex('publication_format_submission_id'));
        }

        if (Schema::hasColumn('publication_formats', 'submission_id')) {
            Schema::dropColumns('publication_formats', 'submission_id');
        }
    }
}
