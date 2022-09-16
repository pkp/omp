<?php

/**
 * @file classes/migration/install/OMPMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPMigration
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OMPMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('identification_codes', function (Blueprint $table) {
            $table->bigInteger('identification_code_id')->autoIncrement();
            $table->bigInteger('publication_format_id');
            $table->string('code', 40);
            $table->string('value', 255);
            $table->index(['identification_code_id', 'publication_format_id', 'code'], 'identification_codes_key');
        });

        Schema::create('publication_dates', function (Blueprint $table) {
            $table->bigInteger('publication_date_id')->autoIncrement();
            $table->bigInteger('publication_format_id');
            $table->string('role', 40);
            $table->string('date_format', 40);
            $table->string('date', 255);
            $table->index(['publication_date_id', 'publication_format_id', 'role'], 'format_publication_dates_pkey');
        });

        Schema::create('sales_rights', function (Blueprint $table) {
            $table->bigInteger('sales_rights_id')->autoIncrement();
            $table->bigInteger('publication_format_id');
            $table->string('type', 40);
            //   ROW is 'rest of world'. ROW sales types have no territories assigned to them
            $table->smallInteger('row_setting')->default(0);
            $table->text('countries_included')->nullable();
            $table->text('countries_excluded')->nullable();
            $table->text('regions_included')->nullable();
            $table->text('regions_excluded')->nullable();
            $table->index(['sales_rights_id', 'publication_format_id'], 'format_sales_rights_pkey');
        });

        Schema::create('markets', function (Blueprint $table) {
            $table->bigInteger('market_id')->autoIncrement();
            $table->bigInteger('publication_format_id');
            $table->text('countries_included')->nullable();
            $table->text('countries_excluded')->nullable();
            $table->text('regions_included')->nullable();
            $table->text('regions_excluded')->nullable();
            $table->string('market_date_role', 40);
            $table->string('market_date_format', 40);
            $table->string('market_date', 255);
            $table->string('price', 255)->nullable();
            $table->string('discount', 255)->nullable();
            $table->string('price_type_code', 255)->nullable();
            $table->string('currency_code', 255)->nullable();
            $table->string('tax_rate_code', 255)->nullable();
            $table->string('tax_type_code', 255)->nullable();
            $table->bigInteger('agent_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->index(['market_id', 'publication_format_id'], 'format_markets_pkey');
        });

        Schema::create('representatives', function (Blueprint $table) {
            $table->bigInteger('representative_id')->autoIncrement();
            $table->bigInteger('submission_id');
            $table->string('role', 40);
            $table->string('representative_id_type', 255)->nullable();
            $table->string('representative_id_value', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('url', 2047)->nullable();
            $table->smallInteger('is_supplier')->default(1);
            $table->index(['representative_id', 'submission_id'], 'format_representatives_pkey');
        });

        Schema::create('features', function (Blueprint $table) {
            $table->bigInteger('submission_id');
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->bigInteger('seq');
            $table->unique(['assoc_type', 'assoc_id', 'submission_id'], 'press_features_pkey');
        });

        Schema::create('new_releases', function (Blueprint $table) {
            $table->bigInteger('submission_id');
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->unique(['assoc_type', 'assoc_id', 'submission_id'], 'new_releases_pkey');
        });

        // Press series.
        Schema::create('series', function (Blueprint $table) {
            $table->bigInteger('series_id')->autoIncrement();
            $table->bigInteger('press_id');
            $table->bigInteger('review_form_id')->nullable();
            //  NOTNULL not included for the sake of 1.1 upgrade, which didn't include this column
            $table->float('seq', 8, 2)->default(0)->nullable();
            $table->smallInteger('featured')->default(0);
            $table->smallInteger('editor_restricted')->default(0);
            $table->string('path', 255);
            $table->text('image')->nullable();
            $table->smallInteger('is_inactive')->default(0);
            $table->index(['press_id'], 'series_press_id');
            $table->unique(['press_id', 'path'], 'series_path');
        });

        // Series-specific settings
        Schema::create('series_settings', function (Blueprint $table) {
            $table->bigInteger('series_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->unique(['series_id', 'locale', 'setting_name'], 'series_settings_pkey');
        });

        // Associations for categories within a series.
        Schema::create('series_categories', function (Blueprint $table) {
            $table->bigInteger('series_id');
            $table->bigInteger('category_id');
            $table->unique(['series_id', 'category_id'], 'series_categories_id');
        });

        // Publications
        Schema::create('publications', function (Blueprint $table) {
            $table->bigInteger('publication_id')->autoIncrement();
            $table->date('date_published')->nullable();
            $table->datetime('last_modified')->nullable();
            $table->bigInteger('primary_contact_id')->nullable();
            $table->string('publication_date_type', 32)->default('pub')->nullable();
            //  PUBLICATION_TYPE_PUBLICATION
            $table->string('publication_type', 32)->default('publication')->nullable();
            $table->float('seq', 8, 2)->default(0);
            $table->bigInteger('series_id')->nullable();
            $table->string('series_position', 255)->nullable();
            $table->bigInteger('submission_id');
            $table->smallInteger('status')->default(1);
            $table->string('url_path', 64)->nullable();
            $table->bigInteger('version')->nullable();
            $table->index(['submission_id'], 'publications_submission_id');
            $table->index(['series_id'], 'publications_section_id');
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Publication formats assigned to published submissions
        Schema::create('publication_formats', function (Blueprint $table) {
            $table->bigInteger('publication_format_id')->autoIncrement();
            $table->bigInteger('publication_id');
            //  DEPRECATED: Held over for the OJS 2.x to 3. upgrade process pkp/pkp-lib#3572
            $table->bigInteger('submission_id')->nullable();
            $table->smallInteger('physical_format')->default(1)->nullable();
            $table->string('entry_key', 64)->nullable();
            $table->float('seq', 8, 2)->default(0);
            $table->string('file_size', 255)->nullable();
            $table->string('front_matter', 255)->nullable();
            $table->string('back_matter', 255)->nullable();
            $table->string('height', 255)->nullable();
            $table->string('height_unit_code', 255)->nullable();
            $table->string('width', 255)->nullable();
            $table->string('width_unit_code', 255)->nullable();
            $table->string('thickness', 255)->nullable();
            $table->string('thickness_unit_code', 255)->nullable();
            $table->string('weight', 255)->nullable();
            $table->string('weight_unit_code', 255)->nullable();
            $table->string('product_composition_code', 255)->nullable();
            $table->string('product_form_detail_code', 255)->nullable();
            $table->string('country_manufacture_code', 255)->nullable();
            $table->string('imprint', 255)->nullable();
            $table->string('product_availability_code', 255)->nullable();
            $table->string('technical_protection_code', 255)->nullable();
            $table->string('returnable_indicator_code', 255)->nullable();
            $table->string('remote_url', 2047)->nullable();
            $table->string('url_path', 64)->nullable();
            $table->smallInteger('is_approved')->default(0);
            $table->smallInteger('is_available')->default(0);
            $table->index(['submission_id'], 'publication_format_submission_id');
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Publication Format metadata.
        Schema::create('publication_format_settings', function (Blueprint $table) {
            $table->bigInteger('publication_format_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->index(['publication_format_id'], 'publication_format_id_key');
            $table->unique(['publication_format_id', 'locale', 'setting_name'], 'publication_format_settings_pkey');
        });

        Schema::create('submission_chapters', function (Blueprint $table) {
            $table->bigInteger('chapter_id')->autoIncrement();
            $table->bigInteger('primary_contact_id')->nullable();
            $table->bigInteger('publication_id');
            $table->float('seq', 8, 2)->default(0);
            $table->bigInteger('source_chapter_id')->nullable();
            $table->index(['chapter_id'], 'chapters_chapter_id');
            $table->index(['publication_id'], 'chapters_publication_id');
            $table->foreign('source_chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('set null');
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Language dependent monograph chapter metadata.
        Schema::create('submission_chapter_settings', function (Blueprint $table) {
            $table->bigInteger('chapter_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->index(['chapter_id'], 'submission_chapter_settings_chapter_id');
            $table->unique(['chapter_id', 'locale', 'setting_name'], 'submission_chapter_settings_pkey');
        });

        Schema::create('submission_chapter_authors', function (Blueprint $table) {
            $table->bigInteger('author_id');
            $table->bigInteger('chapter_id');
            $table->smallInteger('primary_contact')->default(0);
            $table->float('seq', 8, 2)->default(0);
            $table->unique(['author_id', 'chapter_id'], 'chapter_authors_pkey');
        });

        // Add doiId to submission files
        Schema::table('submission_files', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });


        // Spotlights
        Schema::create('spotlights', function (Blueprint $table) {
            $table->bigInteger('spotlight_id')->autoIncrement();
            $table->smallInteger('assoc_type');
            $table->smallInteger('assoc_id');
            $table->bigInteger('press_id');
            $table->index(['assoc_type', 'assoc_id'], 'spotlights_assoc');
        });

        // Spotlight metadata.
        Schema::create('spotlight_settings', function (Blueprint $table) {
            $table->bigInteger('spotlight_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object|date)');
            $table->index(['spotlight_id'], 'spotlight_settings_id');
            $table->unique(['spotlight_id', 'locale', 'setting_name'], 'spotlight_settings_pkey');
        });

        // Logs queued (unfulfilled) payments.
        Schema::create('queued_payments', function (Blueprint $table) {
            $table->bigInteger('queued_payment_id')->autoIncrement();
            $table->datetime('date_created');
            $table->datetime('date_modified');
            $table->date('expiry_date')->nullable();
            $table->text('payment_data')->nullable();
        });

        // Logs completed (fulfilled) payments.
        Schema::create('completed_payments', function (Blueprint $table) {
            $table->bigInteger('completed_payment_id')->autoIncrement();
            $table->datetime('timestamp');
            $table->bigInteger('payment_type');
            $table->bigInteger('context_id');
            $table->bigInteger('user_id')->nullable();
            //  NOTE: assoc_id NOT numeric to incorporate file idents
            $table->string('assoc_id', 16)->nullable();
            $table->float('amount', 8, 2);
            $table->string('currency_code_alpha', 3)->nullable();
            $table->string('payment_method_plugin_name', 80)->nullable();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('completed_payments');
        Schema::drop('identification_codes');
        Schema::drop('publication_dates');
        Schema::drop('sales_rights');
        Schema::drop('markets');
        Schema::drop('representatives');
        Schema::drop('features');
        Schema::drop('new_releases');
        Schema::drop('series');
        Schema::drop('series_settings');
        Schema::drop('series_categories');
        Schema::drop('publications');
        Schema::drop('publication_formats');
        Schema::drop('publication_format_settings');
        Schema::drop('submission_chapters');
        Schema::drop('submission_chapter_settings');
        Schema::drop('submission_chapter_authors');
        Schema::drop('spotlights');
        Schema::drop('spotlight_settings');
        Schema::drop('queued_payments');
        Schema::drop('completed_payments');
    }
}
