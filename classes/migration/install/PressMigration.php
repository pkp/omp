<?php

/**
 * @file classes/migration/install/PressMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressMigration
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PressMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Presses and basic press settings.
        Schema::create('presses', function (Blueprint $table) {
            $table->bigInteger('press_id')->autoIncrement();
            $table->string('path', 32);
            $table->float('seq', 8, 2)->default(0);
            $table->string('primary_locale', 14);
            $table->smallInteger('enabled')->default(1);
            $table->unique(['path'], 'press_path');
        });

        // Press settings.
        Schema::create('press_settings', function (Blueprint $table) {
            $table->bigInteger('press_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->nullable();
            $table->index(['press_id'], 'press_settings_press_id');
            $table->unique(['press_id', 'locale', 'setting_name'], 'press_settings_pkey');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('presses');
        Schema::drop('press_settings');
    }
}
