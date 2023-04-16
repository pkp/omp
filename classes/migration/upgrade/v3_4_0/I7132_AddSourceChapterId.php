<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7132_AddSourceChapterId.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7132_AddSourceChapterId
 *
 * @brief Add column source_chapter_id to submission_chapters table.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\Schema;

class I7132_AddSourceChapterId extends \PKP\migration\Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // pkp/pkp-lib#7132 Add source_chapter_id
        Schema::table('submission_chapters', function ($table) {
            $table->bigInteger('source_chapter_id')->nullable();
            $table->foreign('source_chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('set null');
            $table->index(['source_chapter_id'], 'submission_chapters_source_chapter_id');
        });
    }

    /**
     * Reverse the downgrades
     */
    public function down(): void
    {
        //remove source_chapter_id
        Schema::table('submission_chapters', function ($table) {
            $table->dropForeign('submission_chapters_source_chapter_id_foreign');
            $table->dropColumn('source_chapter_id');
        });
    }
}
