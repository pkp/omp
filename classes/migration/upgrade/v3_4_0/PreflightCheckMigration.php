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

    protected function getEntityRelationships(): array
    {
        return [
            $this->getContextTable() => ['submissions', 'user_groups', 'series', 'categories', 'navigation_menu_items', 'filters', 'genres', 'announcement_types', 'navigation_menus', 'spotlights', 'notifications', 'library_files', 'email_templates', $this->getContextSettingsTable(), 'plugin_settings', 'user_group_stage', 'subeditor_submission_group', 'notification_subscription_settings', 'completed_payments'],
            'submissions' => ['publication_formats', 'submission_files', 'publications', 'review_rounds', 'review_assignments', 'submission_search_objects', 'library_files', 'submission_settings', 'submission_comments', 'stage_assignments', 'review_round_files', 'representatives', 'new_releases', 'features', 'edit_decisions'],
            'users' => ['submission_files', 'review_assignments', 'email_log', 'notifications', 'event_log', 'user_user_groups', 'user_settings', 'user_interests', 'temporary_files', 'submission_comments', 'subeditor_submission_group', 'stage_assignments', 'sessions', 'query_participants', 'notification_subscription_settings', 'notes', 'email_log_users', 'edit_decisions', 'completed_payments', 'access_keys'],
            'submission_files' => ['submission_files', 'submission_file_settings', 'submission_file_revisions', 'review_round_files', 'review_files'],
            'publication_formats' => ['sales_rights', 'publication_format_settings', 'publication_dates', 'markets', 'identification_codes'],
            'submission_chapters' => ['submission_chapters', 'submission_chapter_settings', 'submission_chapter_authors'],
            'publications' => ['submissions', 'publication_formats', 'submission_chapters', 'authors', 'citations', 'publication_settings', 'publication_categories'],
            'user_groups' => ['authors', 'user_user_groups', 'user_group_stage', 'user_group_settings', 'subeditor_submission_group', 'stage_assignments'],
            'series' => ['publications', 'series_settings', 'series_categories'],
            'authors' => ['submission_chapters', 'publications', 'submission_chapter_authors', 'author_settings'],
            'categories' => ['categories', 'series_categories', 'publication_categories', 'category_settings'],
            'review_forms' => ['series', 'review_assignments', 'review_form_elements', 'review_form_settings'],
            'review_rounds' => ['review_assignments', 'review_round_files', 'edit_decisions'],
            'data_object_tombstones' => ['data_object_tombstone_settings', 'data_object_tombstone_oai_set_objects'],
            'files' => ['submission_files', 'submission_file_revisions'],
            'filters' => ['filters', 'filter_settings'],
            'genres' => ['submission_files', 'genre_settings'],
            'navigation_menu_item_assignments' => ['navigation_menu_item_assignments', 'navigation_menu_item_assignment_settings'],
            'navigation_menu_items' => ['navigation_menu_item_assignments', 'navigation_menu_item_settings'],
            'announcement_types' => ['announcements', 'announcement_type_settings'],
            'review_assignments' => ['review_form_responses', 'review_files'],
            'review_form_elements' => ['review_form_responses', 'review_form_element_settings'],
            'controlled_vocab_entries' => ['user_interests', 'controlled_vocab_entry_settings'],
            'queries' => ['query_participants'],
            'navigation_menus' => ['navigation_menu_item_assignments'],
            'library_files' => ['library_file_settings'],
            'event_log' => ['event_log_settings'],
            'email_templates' => ['email_templates_settings'],
            'email_log' => ['email_log_users'],
            'spotlights' => ['spotlight_settings'],
            'static_pages' => ['static_page_settings'],
            'controlled_vocabs' => ['controlled_vocab_entries'],
            'citations' => ['citation_settings'],
            'submission_search_keyword_list' => ['submission_search_object_keywords'],
            'submission_search_objects' => ['submission_search_object_keywords'],
            'announcements' => ['announcement_settings'],
            'filter_groups' => ['filters'],
            'notifications' => ['notification_settings']
        ];
    }
}
