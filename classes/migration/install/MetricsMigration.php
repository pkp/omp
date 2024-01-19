<?php

/**
 * @file classes/migration/install/MetricsMigration.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MetricsMigration
 *
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as Schema;

class MetricsMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metrics_context', function (Blueprint $table) {
            $table->comment('Daily statistics for views of the homepage.');
            $table->bigIncrements('metrics_context_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'metrics_context_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_context_context_id');

            $table->date('date');
            $table->integer('metric');
        });
        Schema::create('metrics_series', function (Blueprint $table) {
            $table->comment('Daily statistics for views of published submissions in each series.');
            $table->bigIncrements('metrics_series_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'metrics_series_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_series_context_id');

            $table->bigInteger('series_id');
            $table->foreign('series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'metrics_series_series_id');

            $table->date('date');
            $table->integer('metric');

            $table->index(['context_id', 'series_id'], 'metrics_series_context_id_series_id');
        });
        Schema::create('metrics_submission', function (Blueprint $table) {
            $table->comment('Daily statistics for views and downloads of published submissions and files.');
            $table->bigIncrements('metrics_submission_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'ms_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_submission_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'metrics_submission_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'metrics_submission_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'metrics_submission_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'metrics_submission_submission_file_id');

            $table->bigInteger('file_type')->nullable();
            $table->bigInteger('assoc_type');
            $table->date('date');
            $table->integer('metric');

            $table->index(['context_id', 'submission_id', 'assoc_type', 'file_type'], 'ms_context_id_submission_id_assoc_type_file_type');
        });
        Schema::create('metrics_counter_submission_daily', function (Blueprint $table) {
            $table->comment('Daily statistics matching the COUNTER R5 protocol for views and downloads of published submissions and files.');
            $table->bigIncrements('metrics_counter_submission_daily_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'msd_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msd_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_counter_submission_daily_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msd_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'metrics_counter_submission_daily_submission_id');

            $table->date('date');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');

            $table->index(['context_id', 'submission_id'], 'msd_context_id_submission_id');
            $table->unique(['load_id', 'context_id', 'submission_id', 'date'], 'msd_uc_load_id_context_id_submission_id_date');
        });
        Schema::create('metrics_counter_submission_monthly', function (Blueprint $table) {
            $table->comment('Monthly statistics matching the COUNTER R5 protocol for views and downloads of published submissions and files.');
            $table->bigIncrements('metrics_counter_submission_monthly_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msm_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_counter_submission_monthly_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msm_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'metrics_counter_submission_monthly_submission_id');

            $table->integer('month');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');

            $table->index(['context_id', 'submission_id'], 'msm_context_id_submission_id');
            $table->unique(['context_id', 'submission_id', 'month'], 'msm_uc_context_id_submission_id_month');
        });
        Schema::create('metrics_counter_submission_institution_daily', function (Blueprint $table) {
            $table->comment('Daily statistics matching the COUNTER R5 protocol for views and downloads from institutions.');
            $table->bigIncrements('metrics_counter_submission_institution_daily_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'msid_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msid_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'msid_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msid_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'msid_submission_id');

            $table->bigInteger('institution_id');
            $table->foreign('institution_id', 'msid_institution_id_foreign')->references('institution_id')->on('institutions')->onDelete('cascade');
            $table->index(['institution_id'], 'msid_institution_id');

            $table->date('date');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');

            $table->index(['context_id', 'submission_id'], 'msid_context_id_submission_id');
            $table->unique(['load_id', 'context_id', 'submission_id', 'institution_id', 'date'], 'msid_uc_load_id_context_id_submission_id_institution_id_date');
        });
        Schema::create('metrics_counter_submission_institution_monthly', function (Blueprint $table) {
            $table->comment('Monthly statistics matching the COUNTER R5 protocol for views and downloads from institutions.');
            $table->bigIncrements('metrics_counter_submission_institution_monthly_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msim_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'msim_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msim_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'msim_submission_id');

            $table->bigInteger('institution_id');
            $table->foreign('institution_id', 'msim_institution_id_foreign')->references('institution_id')->on('institutions')->onDelete('cascade');
            $table->index(['institution_id'], 'msim_institution_id');

            $table->integer('month');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');

            $table->index(['context_id', 'submission_id'], 'msim_context_id_submission_id');
            $table->unique(['context_id', 'submission_id', 'institution_id', 'month'], 'msim_uc_context_id_submission_id_institution_id_month');
        });
        Schema::create('metrics_submission_geo_daily', function (Blueprint $table) {
            $table->comment('Daily statistics by country, region and city for views and downloads of published submissions and files.');
            $table->bigIncrements('metrics_submission_geo_daily_id');

            $table->string('load_id', 50);
            $table->index(['load_id'], 'msgd_load_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msgd_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_submission_geo_daily_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msgd_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'metrics_submission_geo_daily_submission_id');

            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->date('date');
            $table->integer('metric');
            $table->integer('metric_unique');

            $table->index(['context_id', 'submission_id'], 'msgd_context_id_submission_id');
            switch (DB::getDriverName()) {
                case 'mysql':
                    // See "Create a database table" here: https://db-ip.com/db/format/ip-to-city-lite/csv.html
                    // where city is defined as varchar(80)
                    $table->unique([DB::raw('load_id, context_id, submission_id, country, region, city(80), date')], 'msgd_uc_load_context_submission_c_r_c_date');
                    break;
                case 'pgsql':
                    $table->unique(['load_id', 'context_id', 'submission_id', 'country', 'region', 'city', 'date'], 'msgd_uc_load_context_submission_c_r_c_date');
                    break;
            }
        });
        Schema::create('metrics_submission_geo_monthly', function (Blueprint $table) {
            $table->comment('Monthly statistics by country, region and city for views and downloads of published submissions and files.');
            $table->bigIncrements('metrics_submission_geo_monthly_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'msgm_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'metrics_submission_geo_monthly_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'msgm_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'metrics_submission_geo_monthly_submission_id');

            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->integer('month');
            $table->integer('metric');
            $table->integer('metric_unique');

            $table->index(['context_id', 'submission_id'], 'msgm_context_id_submission_id');
            switch (DB::getDriverName()) {
                case 'mysql':
                    // See "Create a database table" here: https://db-ip.com/db/format/ip-to-city-lite/csv.html
                    // where city is defined as varchar(80)
                    $table->unique([DB::raw('context_id, submission_id, country, region, city(80), month')], 'msgm_uc_context_submission_c_r_c_month');
                    break;
                case 'pgsql':
                    $table->unique(['context_id', 'submission_id', 'country', 'region', 'city', 'month'], 'msgm_uc_context_submission_c_r_c_month');
                    break;
            }
        });

        // Usage stats total book and chapter item temporary records
        Schema::create('usage_stats_total_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats totals based on visitor log records. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_total_id');

            $table->dateTime('date', $precision = 0);
            $table->string('ip', 64);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->string('canonical_url', 255);

            $table->bigInteger('series_id')->nullable();
            $table->foreign('series_id', 'ust_series_id_foreign')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'ust_series_id');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'ust_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'ust_context_id');

            $table->bigInteger('submission_id')->nullable();
            $table->foreign('submission_id', 'ust_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'ust_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id', 'ust_chapter_id_foreign')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'ust_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id', 'ust_representation_id_foreign')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'ust_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id', 'ust_submission_file_id_foreign')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'ust_submission_file_id');

            $table->bigInteger('assoc_type');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 50);

            $table->index(['load_id', 'context_id', 'ip'], 'ust_load_id_context_id_ip');
        });

        // Usage stats unique book and chapter item investigations temporary records
        // No need to consider series_id here because investigations are only relevant/calculated on submission level.
        Schema::create('usage_stats_unique_item_investigations_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats on unique downloads based on visitor log records. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_unique_item_id');

            $table->dateTime('date', $precision = 0);
            $table->string('ip', 64);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'usii_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'usii_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'usii_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'usii_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id', 'usii_chapter_id_foreign')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'usii_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id', 'usii_representation_id_foreign')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'usii_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id', 'usii_submission_file_id_foreign')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'usii_submission_file_id');

            $table->bigInteger('assoc_type');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 50);

            $table->index(['load_id', 'context_id', 'ip'], 'usii_load_id_context_id_ip');
        });

        // Usage stats unique book and chapter item requests temporary records
        // No need to consider series_id here because requests are only relevant/calculated on submission level.
        Schema::create('usage_stats_unique_item_requests_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats on unique views based on visitor log records. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_item_id');

            $table->dateTime('date', $precision = 0);
            $table->string('ip', 64);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'usir_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'usir_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'usir_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'usir_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id', 'usir_chapter_id_foreign')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'usir_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id', 'usir_representation_id_foreign')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'usir_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id', 'usir_submission_file_id_foreign')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'usir_submission_file_id');

            $table->bigInteger('assoc_type');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 50);

            $table->index(['load_id', 'context_id', 'ip'], 'usir_load_id_context_id_ip');
        });

        // Usage stats unique title investigations temporary records
        // No need to consider series_id here because investigations are only relevant/calculated on submission level.
        Schema::create('usage_stats_unique_title_investigations_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats for views and downloads from institutions based on visitor log records. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_unique_investigations_id');
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 64);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'usti_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'usti_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'usti_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'usti_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id', 'usti_chapter_id_foreign')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'usti_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id', 'usti_representation_id_foreign')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'usti_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id', 'usti_submission_file_id_foreign')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'usti_submission_file_id');

            $table->bigInteger('assoc_type');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 50);

            $table->index(['load_id', 'context_id', 'ip'], 'usti_load_id_context_id_ip');
        });

        // Usage stats unique title requests temporary records
        // No need to consider series_id here because requests are only relevant/calculated on submission level.
        Schema::create('usage_stats_unique_title_requests_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats for unique title requests. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_unique_requests_id');

            $table->dateTime('date', $precision = 0);
            $table->string('ip', 64);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');

            $table->bigInteger('context_id');
            $table->foreign('context_id', 'ustr_context_id_foreign')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'ustr_context_id');

            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'ustr_submission_id_foreign')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'ustr_submission_id');

            $table->bigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id', 'ustr_chapter_id_foreign')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'ustr_chapter_id');

            $table->bigInteger('representation_id')->nullable();
            $table->foreign('representation_id', 'ustr_representation_id_foreign')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['representation_id'], 'ustr_representation_id');

            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->foreign('submission_file_id', 'ustr_submission_file_id_foreign')->references('submission_file_id')->on('submission_files')->onDelete('cascade');
            $table->index(['submission_file_id'], 'ustr_submission_file_id');

            $table->bigInteger('assoc_type');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 50);

            $table->index(['load_id', 'context_id', 'ip'], 'ustr_load_id_context_id_ip');
        });

        // Usage stats institution temporary records
        Schema::create('usage_stats_institution_temporary_records', function (Blueprint $table) {
            $table->comment('Temporary stats by institution. Data in this table is provisional. See the metrics_* tables for compiled stats.');
            $table->bigIncrements('usage_stats_temp_institution_id');

            $table->string('load_id', 50);
            $table->bigInteger('line_number');

            $table->bigInteger('institution_id');
            $table->foreign('institution_id', 'usi_institution_id_foreign')->references('institution_id')->on('institutions')->onDelete('cascade');
            $table->index(['institution_id'], 'usi_institution_id');

            $table->unique(['load_id', 'line_number', 'institution_id'], 'usi_load_id_line_number_institution_id');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('metrics_context');
        Schema::drop('metrics_series');
        Schema::drop('metrics_submission');
        Schema::drop('metrics_counter_submission_daily');
        Schema::drop('metrics_counter_submission_monthly');
        Schema::drop('metrics_counter_submission_institution_daily');
        Schema::drop('metrics_counter_submission_institution_monthly');
        Schema::drop('metrics_submission_geo_daily');
        Schema::drop('metrics_submission_geo_monthly');
        Schema::drop('usage_stats_total_temporary_records');
        Schema::drop('usage_stats_unique_item_investigations_temporary_records');
        Schema::drop('usage_stats_unique_item_requests_temporary_records');
        Schema::drop('usage_stats_unique_title_investigations_temporary_records');
        Schema::drop('usage_stats_unique_title_requests_temporary_records');
        Schema::drop('usage_stats_institution_temporary_records');
    }
}
