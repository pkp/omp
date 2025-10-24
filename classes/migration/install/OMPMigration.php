<?php

/**
 * @file classes/migration/install/OMPMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPMigration
 *
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use APP\publication\enums\VersionStage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OMPMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Publications
        Schema::create('publications', function (Blueprint $table) {
            $table->comment('Each publication is one version of a submission.');
            $table->bigInteger('publication_id')->autoIncrement();
            $table->date('date_published')->nullable();
            $table->datetime('last_modified')->nullable();

            $table->bigInteger('primary_contact_id')->nullable();
            $table->foreign('primary_contact_id', 'publications_author_id')->references('author_id')->on('authors')->onDelete('set null');
            $table->index(['primary_contact_id'], 'publications_primary_contact_id');

            $table->string('publication_date_type', 32)->default('pub')->nullable();
            //  PUBLICATION_TYPE_PUBLICATION
            $table->string('publication_type', 32)->default('publication')->nullable();
            $table->float('seq')->default(0);

            // FK relationship is defined where series table is created
            $table->bigInteger('series_id')->nullable();
            $table->index(['series_id'], 'publications_section_id');

            $table->string('series_position', 255)->nullable();

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'publications_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'publications_submission_id');

            $table->smallInteger('status')->default(1);
            $table->string('url_path', 64)->nullable();

            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'publications_doi_id');

            $table->enum('version_stage', array_column(VersionStage::cases(), 'value'))->nullable();
            $table->integer('version_minor')->nullable();
            $table->integer('version_major')->nullable();
            $table->datetime('created_at')->useCurrent();

            $table->bigInteger('source_publication_id')->nullable();
            $table->foreign('source_publication_id', 'publications_source_publication_id')
                ->references('publication_id')->on('publications')->nullOnDelete();
            $table->index(['source_publication_id'], 'publications_source_publication_id_index');
        });
        // The following foreign key relationships are for tables defined in SubmissionsMigration
        // but they depend on publications to exist so are created here.
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('current_publication_id', 'submissions_publication_id')->references('publication_id')->on('publications')->onDelete('set null');
        });
        Schema::table('publication_settings', function (Blueprint $table) {
            $table->foreign('publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
        });
        Schema::table('authors', function (Blueprint $table) {
            $table->foreign('publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
        });
        Schema::table('review_rounds', function (Blueprint $table) {
            $table->foreign('publication_id')->references('publication_id')->on('publications');
            $table->index(['publication_id'], 'review_rounds_publication_id');
        });

        // Publication formats assigned to published submissions
        Schema::create('publication_formats', function (Blueprint $table) {
            $table->comment('Publication formats are representations of a publication in a particular format, e.g. PDF, hardcover, etc. Each publication format may contain many chapters.');
            $table->bigInteger('publication_format_id')->autoIncrement();

            $table->bigInteger('publication_id');
            $table->foreign('publication_id', 'publication_formats_publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
            $table->index(['publication_id'], 'publication_formats_publication_id');

            $table->smallInteger('physical_format')->default(1)->nullable();
            $table->string('entry_key', 64)->nullable();
            $table->float('seq')->default(0);
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

            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'publication_formats_doi_id');
        });

        // Publication Format metadata.
        Schema::create('publication_format_settings', function (Blueprint $table) {
            $table->comment('More data about publication formats, including localized properties.');
            $table->bigIncrements('publication_format_setting_id');

            $table->bigInteger('publication_format_id');
            $table->foreign('publication_format_id', 'publication_format_settings_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'publication_format_id_key');

            $table->string('locale', 28)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->unique(['publication_format_id', 'locale', 'setting_name'], 'publication_format_settings_unique');
        });

        Schema::create('identification_codes', function (Blueprint $table) {
            $table->comment('ONIX identification codes for publication formats.');
            $table->bigInteger('identification_code_id')->autoIncrement();

            $table->bigInteger('publication_format_id');
            $table->foreign('publication_format_id', 'identification_codes_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'identification_codes_publication_format_id');

            $table->string('code', 40);
            $table->string('value', 255);

            $table->index(['identification_code_id', 'publication_format_id', 'code'], 'identification_codes_key');
        });

        Schema::create('publication_dates', function (Blueprint $table) {
            $table->comment('ONIX publication dates for publication formats.');
            $table->bigInteger('publication_date_id')->autoIncrement();

            $table->bigInteger('publication_format_id');
            $table->foreign('publication_format_id', 'publication_dates_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'publication_dates_publication_format_id');

            $table->string('role', 40);
            $table->string('date_format', 40);
            $table->string('date', 255);

            $table->index(['publication_date_id', 'publication_format_id', 'role'], 'format_publication_dates_pkey');
        });

        Schema::create('sales_rights', function (Blueprint $table) {
            $table->comment('ONIX sales rights for publication formats.');
            $table->bigInteger('sales_rights_id')->autoIncrement();

            $table->bigInteger('publication_format_id');
            $table->foreign('publication_format_id', 'sales_rights_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'sales_rights_publication_format_id');

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
            $table->comment('ONIX market information for publication formats.');
            $table->bigInteger('market_id')->autoIncrement();

            $table->bigInteger('publication_format_id');
            $table->foreign('publication_format_id', 'markets_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'markets_publication_format_id');

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

            // FIXME: These columns don't appear to be used
            $table->bigInteger('agent_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();

            $table->index(['market_id', 'publication_format_id'], 'format_markets_pkey');
        });

        Schema::create('representatives', function (Blueprint $table) {
            $table->comment('ONIX representatives for publication formats.');
            $table->bigInteger('representative_id')->autoIncrement();

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'representatives_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'representatives_submission_id');

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
            $table->comment('Information about which submissions are featured in the press.');
            $table->bigIncrements('feature_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'features_submission_id');

            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');

            $table->bigInteger('seq');

            $table->unique(['assoc_type', 'assoc_id', 'submission_id'], 'press_features_unique');
        });

        Schema::create('new_releases', function (Blueprint $table) {
            $table->comment('Information about which submissions in the press are considered new releases.');
            $table->bigIncrements('new_release_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'new_releases_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'new_releases_submission_id');

            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');

            $table->unique(['assoc_type', 'assoc_id', 'submission_id'], 'new_releases_unique');
        });

        // Press series.
        Schema::create('series', function (Blueprint $table) {
            $table->comment('A list of press series, into which submissions can be organized.');
            $table->bigInteger('series_id')->autoIncrement();

            $table->bigInteger('press_id');
            $table->foreign('press_id', 'series_press_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['press_id'], 'series_press_id');

            $table->bigInteger('review_form_id')->nullable();
            $table->foreign('review_form_id', 'series_review_form_id')->references('review_form_id')->on('review_forms')->onDelete('set null');
            $table->index(['review_form_id'], 'series_review_form_id');

            //  NOT NULL not included for the sake of 1.1 upgrade, which didn't include this column
            $table->float('seq')->default(0)->nullable();

            $table->smallInteger('featured')->default(0);
            $table->smallInteger('editor_restricted')->default(0);
            $table->string('path', 255);
            $table->text('image')->nullable();
            $table->smallInteger('is_inactive')->default(0);

            $table->unique(['press_id', 'path'], 'series_path');
        });
        Schema::table('publications', function (Blueprint $table) {
            $table->foreign('series_id', 'publications_series_id')->references('series_id')->on('series')->onDelete('set null');
        });

        // Series-specific settings
        Schema::create('series_settings', function (Blueprint $table) {
            $table->comment('More data about series, including localized properties such as series titles.');
            $table->bigIncrements('series_setting_id');

            $table->bigInteger('series_id');
            $table->foreign('series_id', 'series_settings_series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'series_settings_series_id');

            $table->string('locale', 28)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();

            $table->unique(['series_id', 'locale', 'setting_name'], 'series_settings_unique');
        });

        Schema::create('submission_chapters', function (Blueprint $table) {
            $table->comment('A list of chapters for each submission (when submissions are divided into chapters).');
            $table->bigInteger('chapter_id')->autoIncrement();
            $table->index(['chapter_id'], 'chapters_chapter_id');

            $table->bigInteger('primary_contact_id')->nullable();
            $table->foreign('primary_contact_id')->references('author_id')->on('authors')->onDelete('set null');
            $table->index(['primary_contact_id'], 'submission_chapters_primary_contact_id');

            $table->bigInteger('publication_id');
            $table->foreign('publication_id', 'submission_chapters_publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
            $table->index(['publication_id'], 'submission_chapters_publication_id');

            $table->float('seq')->default(0);

            // FK defined below (circular reference)
            $table->bigInteger('source_chapter_id')->nullable();
            $table->index(['source_chapter_id'], 'submission_chapters_source_chapter_id');

            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });
        Schema::table('submission_chapters', function (Blueprint $table) {
            $table->foreign('source_chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('set null');
        });

        // Language dependent monograph chapter metadata.
        Schema::create('submission_chapter_settings', function (Blueprint $table) {
            $table->comment('More information about submission chapters, including localized properties such as chapter titles.');
            $table->bigIncrements('submission_chapter_setting_id');

            $table->bigInteger('chapter_id');

            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'submission_chapter_settings_chapter_id');

            $table->string('locale', 28)->default('');
            $table->string('setting_name', 255);
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');

            $table->unique(['chapter_id', 'locale', 'setting_name'], 'submission_chapter_settings_unique');
        });

        Schema::create('submission_chapter_authors', function (Blueprint $table) {
            $table->comment('The list of authors associated with each submission chapter.');
            $table->bigInteger('author_id');
            $table->foreign('author_id')->references('author_id')->on('authors')->onDelete('cascade');
            $table->index(['author_id'], 'submission_chapter_authors_author_id');

            $table->bigInteger('chapter_id');
            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'submission_chapter_authors_chapter_id');

            $table->smallInteger('primary_contact')->default(0);
            $table->float('seq')->default(0);

            $table->unique(['author_id', 'chapter_id'], 'chapter_authors_pkey');
        });

        // Add doi_id to submission files
        Schema::table('submission_files', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'submission_files_doi_id');
        });

        // Logs queued (unfulfilled) payments.
        Schema::create('queued_payments', function (Blueprint $table) {
            $table->comment('A list of queued (unfilled) payments, i.e. payments that have not yet been completed via an online payment system.');
            $table->bigInteger('queued_payment_id')->autoIncrement();
            $table->datetime('date_created');
            $table->datetime('date_modified');
            $table->date('expiry_date')->nullable();
            $table->text('payment_data')->nullable();
        });

        // Logs completed (fulfilled) payments.
        Schema::create('completed_payments', function (Blueprint $table) {
            $table->comment('A list of completed (fulfilled) payments, with information about the type of payment and the entity it relates to.');
            $table->bigInteger('completed_payment_id')->autoIncrement();
            $table->datetime('timestamp');
            $table->bigInteger('payment_type');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'completed_payments_context_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'completed_payments_context_id');

            $table->bigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['user_id'], 'completed_payments_user_id');

            //  NOTE: assoc_id NOT numeric to incorporate file idents
            $table->string('assoc_id', 16)->nullable();
            $table->decimal('amount', 8, 2);
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
        Schema::drop('publications');
        Schema::drop('submission_chapters');
        Schema::drop('submission_chapter_settings');
        Schema::drop('submission_chapter_authors');
        Schema::drop('queued_payments');
        Schema::drop('completed_payments');
        Schema::drop('publication_formats');
        Schema::drop('publication_format_settings');
    }
}
