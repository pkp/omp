<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I6093_AddForeignKeys.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I6093_AddForeignKeys
 *
 * @brief Describe upgrade/downgrade operations for introducing foreign key definitions to existing database relationships.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class I6093_AddForeignKeys extends \PKP\migration\upgrade\v3_4_0\I6093_AddForeignKeys
{
    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    protected function getContextKeyField(): string
    {
        return 'press_id';
    }

    public function up(): void
    {
        parent::up();

        Schema::table('spotlights', function (Blueprint $table) {
            $table->foreign('press_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['press_id'], 'spotlights_press_id');
        });
        Schema::table('spotlight_settings', function (Blueprint $table) {
            $table->foreign('spotlight_id')->references('spotlight_id')->on('spotlights')->onDelete('cascade');
            $table->index(['spotlight_id'], 'spotlight_settings_spotlight_id');
        });
        Schema::table('series_settings', function (Blueprint $table) {
            $table->foreign('series_id', 'series_settings_series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'series_settings_series_id');
        });
        Schema::table('series_categories', function (Blueprint $table) {
            $table->foreign('series_id', 'series_categories_series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->index(['series_id'], 'series_categories_series_id');

            $table->foreign('category_id', 'series_categories_category_id')->references('category_id')->on('categories')->onDelete('cascade');
            $table->index(['category_id'], 'series_categories_category_id');
        });
        Schema::table('series', function (Blueprint $table) {
            $table->foreign('press_id', 'series_press_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->foreign('review_form_id', 'series_review_form_id')->references('review_form_id')->on('review_forms')->onDelete('set null');
            $table->index(['review_form_id'], 'series_review_form_id');
        });
        Schema::table('completed_payments', function (Blueprint $table) {
            $table->foreign('context_id', 'completed_payments_context_id')->references('press_id')->on('presses')->onDelete('cascade');
            $table->index(['context_id'], 'completed_payments_context_id');

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['user_id'], 'completed_payments_user_id');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->foreign('primary_contact_id', 'publications_author_id')->references('author_id')->on('authors')->onDelete('set null');
            $table->index(['primary_contact_id'], 'publications_primary_contact_id');

            $table->foreign('submission_id', 'publications_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');

            $table->foreign('series_id', 'publications_series_id')->references('series_id')->on('series')->onDelete('set null');
        });

        Schema::table('publication_formats', function (Blueprint $table) {
            $table->foreign('publication_id', 'publication_formats_publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
            $table->index(['publication_id'], 'publication_formats_publication_id');
        });

        Schema::table('submission_chapters', function (Blueprint $table) {
            $table->foreign('publication_id', 'submission_chapters_publication_id')->references('publication_id')->on('publications')->onDelete('cascade');
            $table->index(['publication_id'], 'submission_chapters_publication_id');

            $table->foreign('primary_contact_id')->references('author_id')->on('authors')->onDelete('set null');
            $table->index(['primary_contact_id'], 'submission_chapters_primary_contact_id');
        });

        Schema::table('submission_chapter_settings', function (Blueprint $table) {
            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
        });

        Schema::table('submission_chapter_authors', function (Blueprint $table) {
            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters')->onDelete('cascade');
            $table->index(['chapter_id'], 'submission_chapter_authors_chapter_id');

            $table->foreign('author_id')->references('author_id')->on('authors')->onDelete('cascade');
            $table->index(['author_id'], 'submission_chapter_authors_author_id');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->foreign('publication_format_id', 'markets_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'markets_publication_format_id');
        });

        Schema::table('publication_format_settings', function (Blueprint $table) {
            $table->foreign('publication_format_id', 'publication_format_settings_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'publication_format_settings_publication_format_id');
        });

        Schema::table('publication_dates', function (Blueprint $table) {
            $table->foreign('publication_format_id', 'publication_dates_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'publication_dates_publication_format_id');
        });

        Schema::table('identification_codes', function (Blueprint $table) {
            $table->foreign('publication_format_id', 'identification_codes_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'identification_codes_publication_format_id');
        });

        Schema::table('sales_rights', function (Blueprint $table) {
            $table->foreign('publication_format_id', 'sales_rights_publication_format_id')->references('publication_format_id')->on('publication_formats')->onDelete('cascade');
            $table->index(['publication_format_id'], 'sales_rights_publication_format_id');
        });

        Schema::table('new_releases', function (Blueprint $table) {
            $table->foreign('submission_id', 'new_releases_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'new_releases_submission_id');
        });

        Schema::table('representatives', function (Blueprint $table) {
            $table->foreign('submission_id', 'representatives_submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'representatives_submission_id');
        });

        Schema::table('features', function (Blueprint $table) {
            $table->foreign('submission_id')->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->index(['submission_id'], 'features_submission_id');
        });
    }
}
