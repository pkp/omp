<?php

/**
 * @file classes/migration/upgrade/v3_6_0/I1660_ReviewerRecommendations.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I1660_ReviewerRecommendations.php
 *
 * @brief Upgrade migration to add recommendations
 *
 */

namespace APP\migration\upgrade\v3_6_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class I1660_ReviewerRecommendations extends \PKP\migration\Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->bigInteger('reviewer_recommendation_id')->nullable()->after('reviewer_id');
            $table
                ->foreign('reviewer_recommendation_id')
                ->references('reviewer_recommendation_id')
                ->on('reviewer_recommendations')
                ->onDelete('set null');
            $table->index(['reviewer_recommendation_id'], 'review_assignments_recommendation_id');

            $table->dropColumn('recommendation');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropForeign('review_assignments_reviewer_recommendation_id_foreign');
            $table->dropColumn(['reviewer_recommendation_id']);
            $table->smallInteger('recommendation')->nullable()->after('reviewer_id');
        });
    }
}
