<?php

/**
 * @file classes/migration/install/PressMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressMigration
 *
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
            $table->comment('A list of presses managed by the system.');
            $table->bigInteger('press_id')->autoIncrement();
            $table->string('path', 32);
            $table->float('seq')->default(0);
            $table->string('primary_locale', 28);
            $table->smallInteger('enabled')->default(1);
            $table->unique(['path'], 'press_path');
        });

        // Press settings.
        Schema::create('press_settings', function (Blueprint $table) {
            $table->comment('More data about presses, including localized properties such as policies.');
            $table->bigIncrements('press_setting_id');

            $table->bigInteger('press_id');
            $table->foreign('press_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['press_id'], 'press_settings_press_id');

            $table->string('locale', 28)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();

            $table->unique(['press_id', 'locale', 'setting_name'], 'press_settings_unique');
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
