<?php

/**
 * @file classes/migration/upgrade/v3_4_0/PreflightCheckMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PreflightCheckMigration
 *
 * @brief Check for common problems early in the upgrade process.
 */

namespace APP\migration\upgrade\v3_4_0;

class PreflightCheckMigration extends \PKP\migration\upgrade\v3_4_0\PreflightCheckMigration
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

    protected function buildOrphanedEntityProcessor(): void
    {
        parent::buildOrphanedEntityProcessor();

        $this->addTableProcessor('publication_formats', function (): int {
            $affectedRows = 0;
            // Depends directly on ~2 entities: doi_id->dois.doi_id(not found in previous version) publication_id->publications.publication_id
            $affectedRows += $this->deleteRequiredReference('publication_formats', 'publication_id', 'publications', 'publication_id');
            return $affectedRows;
        });

        $this->addTableProcessor('submission_chapters', function (): int {
            $affectedRows = 0;
            // Depends directly on ~4 entities: doi_id->dois.doi_id(not found in previous version) primary_contact_id->authors.author_id publication_id->publications.publication_id source_chapter_id->submission_chapters.chapter_id
            $affectedRows += $this->deleteRequiredReference('submission_chapters', 'publication_id', 'publications', 'publication_id');

            $affectedRows += $this->cleanOptionalReference('submission_chapters', 'primary_contact_id', 'authors', 'author_id');
            return $affectedRows;
        });

        $this->addTableProcessor('publications', function (): int {
            $affectedRows = 0;
            // Depends directly on ~4 entities: primary_contact_id->authors.author_id doi_id->dois.doi_id(not found in previous version) series_id->series.series_id submission_id->submissions.submission_id
            $affectedRows += $this->cleanOptionalReference('publications', 'series_id', 'series', 'series_id');
            return $affectedRows;
        });

        $this->addTableProcessor('series', function (): int {
            $affectedRows = 0;
            // Depends directly on ~2 entities: press_id->presses.press_id review_form_id->review_forms.review_form_id
            $affectedRows += $this->deleteRequiredReference('series', $this->getContextKeyField(), $this->getContextTable(), $this->getContextKeyField());
            $affectedRows += $this->cleanOptionalReference('series', 'review_form_id', 'review_forms', 'review_form_id');
            return $affectedRows;
        });

        $this->addTableProcessor('spotlights', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: press_id->presses.press_id
            $affectedRows += $this->deleteRequiredReference('spotlights', $this->getContextKeyField(), $this->getContextTable(), $this->getContextKeyField());
            return $affectedRows;
        });

        $this->addTableProcessor('completed_payments', function (): int {
            $affectedRows = 0;
            // Depends directly on ~2 entities: context_id->presses.press_id user_id->users.user_id
            $affectedRows += $this->deleteRequiredReference('completed_payments', 'context_id', $this->getContextTable(), $this->getContextKeyField());
            $affectedRows += $this->deleteOptionalReference('completed_payments', 'user_id', 'users', 'user_id');
            return $affectedRows;
        });

        $this->addTableProcessor('features', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: submission_id->submissions.submission_id
            $affectedRows += $this->deleteRequiredReference('features', 'submission_id', 'submissions', 'submission_id');
            return $affectedRows;
        });

        $this->addTableProcessor('identification_codes', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: publication_format_id->publication_formats.publication_format_id
            $affectedRows += $this->deleteRequiredReference('identification_codes', 'publication_format_id', 'publication_formats', 'publication_format_id');
            return $affectedRows;
        });

        $this->addTableProcessor('markets', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: publication_format_id->publication_formats.publication_format_id
            $affectedRows += $this->deleteRequiredReference('markets', 'publication_format_id', 'publication_formats', 'publication_format_id');
            return $affectedRows;
        });

        $this->addTableProcessor('new_releases', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: submission_id->submissions.submission_id
            $affectedRows += $this->deleteRequiredReference('new_releases', 'submission_id', 'submissions', 'submission_id');
            return $affectedRows;
        });

        $this->addTableProcessor('press_settings', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: press_id->presses.press_id
            $affectedRows += $this->deleteRequiredReference($this->getContextSettingsTable(), $this->getContextKeyField(), $this->getContextTable(), $this->getContextKeyField());
            return $affectedRows;
        });

        $this->addTableProcessor('publication_dates', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: publication_format_id->publication_formats.publication_format_id
            $affectedRows += $this->deleteRequiredReference('publication_dates', 'publication_format_id', 'publication_formats', 'publication_format_id');
            return $affectedRows;
        });

        $this->addTableProcessor('publication_format_settings', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: publication_format_id->publication_formats.publication_format_id
            $affectedRows += $this->deleteRequiredReference('publication_format_settings', 'publication_format_id', 'publication_formats', 'publication_format_id');
            return $affectedRows;
        });

        $this->addTableProcessor('representatives', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: submission_id->submissions.submission_id
            $affectedRows += $this->deleteRequiredReference('representatives', 'submission_id', 'submissions', 'submission_id');
            return $affectedRows;
        });

        $this->addTableProcessor('sales_rights', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: publication_format_id->publication_formats.publication_format_id
            $affectedRows += $this->deleteRequiredReference('sales_rights', 'publication_format_id', 'publication_formats', 'publication_format_id');
            return $affectedRows;
        });

        $this->addTableProcessor('series_categories', function (): int {
            $affectedRows = 0;
            // Depends directly on ~2 entities: category_id->categories.category_id series_id->series.series_id
            $affectedRows += $this->deleteRequiredReference('series_categories', 'series_id', 'series', 'series_id');
            $affectedRows += $this->deleteRequiredReference('series_categories', 'category_id', 'categories', 'category_id');
            return $affectedRows;
        });

        $this->addTableProcessor('series_settings', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: series_id->series.series_id
            $affectedRows += $this->deleteRequiredReference('series_settings', 'series_id', 'series', 'series_id');
            return $affectedRows;
        });

        $this->addTableProcessor('spotlight_settings', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: spotlight_id->spotlights.spotlight_id
            $affectedRows += $this->deleteRequiredReference('spotlight_settings', 'spotlight_id', 'spotlights', 'spotlight_id');
            return $affectedRows;
        });

        $this->addTableProcessor('submission_chapter_authors', function (): int {
            $affectedRows = 0;
            // Depends directly on ~2 entities: author_id->authors.author_id chapter_id->submission_chapters.chapter_id
            $affectedRows += $this->deleteRequiredReference('submission_chapter_authors', 'chapter_id', 'submission_chapters', 'chapter_id');
            $affectedRows += $this->deleteRequiredReference('submission_chapter_authors', 'author_id', 'authors', 'author_id');
            return $affectedRows;
        });

        $this->addTableProcessor('submission_chapter_settings', function (): int {
            $affectedRows = 0;
            // Depends directly on ~1 entities: chapter_id->submission_chapters.chapter_id
            $affectedRows += $this->deleteRequiredReference('submission_chapter_settings', 'chapter_id', 'submission_chapters', 'chapter_id');
            return $affectedRows;
        });
    }
}
