<?php

/**
 * @file classes/migration/install/SeriesCategoriesMigration.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesCategoriesMigration
 *
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeriesCategoriesMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('series_categories', function (Blueprint $table) {
            $table->comment('A list of relationships between series and category information.');
            $table->bigInteger('series_id');
            $table->foreign('series_id', 'series_categories_series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'series_categories_series_id');

            $table->bigInteger('category_id');
            $table->foreign('category_id', 'series_categories_category_id')->references('category_id')->on('categories')->onDelete('cascade');
            $table->index(['category_id'], 'series_categories_category_id');

            $table->unique(['series_id', 'category_id'], 'series_categories_id');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('series_categories');
    }
}
