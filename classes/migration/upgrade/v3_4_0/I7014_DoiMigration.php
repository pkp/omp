<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7014_DoiMigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7014_DoiMigration
 *
 * @brief Describe upgrade/downgrade operations for DB table dois.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\upgrade\v3_4_0\PKPI7014_DoiMigration;

class I7014_DoiMigration extends PKPI7014_DoiMigration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        parent::up();

        // Add doiId to chapters
        Schema::table('submission_chapters', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'submission_chapters_doi_id');
        });

        // Add doiId to publication formats
        Schema::table('publication_formats', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'publication_formats_doi_id');
        });

        // Add doiId to submission files
        Schema::table('submission_files', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
            $table->index(['doi_id'], 'submission_files_doi_id');
        });

        $this->migrateExistingDataUp();
    }

    /**
     * Reverse the downgrades
     *
     * @throws DowngradeNotSupportedException
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }

    protected function migrateExistingDataUp(): void
    {
        parent::migrateExistingDataUp();
        // Find all existing DOIs, move to new DOI objects and add foreign key for pub object
        $this->_migrateRepresentationDoisUp();
        $this->_migrateChapterDoisUp();
        $this->_migrateSubmissionFileDoisUp();
    }

    /**
     * Move representation/publication format DOIs from publication_format_settings table to DOI objects
     */
    private function _migrateRepresentationDoisUp(): void
    {
        $q = DB::table('submissions', 's')
            ->select(['s.context_id', 'pf.publication_format_id', 'pf.doi_id', 'pfss.setting_name', 'pfss.setting_value'])
            ->leftJoin('publications as p', 'p.submission_id', '=', 's.submission_id')
            ->leftJoin('publication_formats as pf', 'p.publication_id', '=', 'pf.publication_id')
            ->leftJoin('publication_format_settings as pfss', 'pf.publication_format_id', '=', 'pfss.publication_format_id')
            ->where('pfss.setting_name', '=', 'pub-id::doi');

        $q->chunkById(1000, function ($items) {
            foreach ($items as $item) {
                // Double-check to ensure a DOI object does not already exist for publication
                if ($item->doi_id === null) {
                    $doiId = $this->_addDoi($item->context_id, $item->setting_value);

                    // Add association to newly created DOI to publication
                    DB::table('publication_formats')
                        ->where('publication_format_id', '=', $item->publication_format_id)
                        ->update(['doi_id' => $doiId]);
                } else {
                    // Otherwise, update existing DOI object
                    $this->_updateDoi($item->doi_id, $item->context_id, $item->setting_value);
                }
            }
        }, 'pf.publication_format_id', 'publication_format_id');

        // Remove pub-id::doi settings entry
        DB::table('publication_settings')
            ->where('setting_name', '=', 'pub-id::doi')
            ->delete();
    }

    /**
     * Move chapter DOIs from submission_chapter_settings table to DOI objects
     */
    private function _migrateChapterDoisUp(): void
    {
        $q = DB::table('submissions', 's')
            ->select(['s.context_id', 'sc.chapter_id', 'sc.doi_id', 'scss.setting_name', 'scss.setting_value'])
            ->leftJoin('publications as p', 'p.submission_id', '=', 's.submission_id')
            ->leftJoin('submission_chapters as sc', 'p.publication_id', '=', 'sc.publication_id')
            ->leftJoin('submission_chapter_settings as scss', 'sc.chapter_id', '=', 'scss.chapter_id')
            ->where('scss.setting_name', '=', 'pub-id::doi');

        $q->chunkById(1000, function ($items) {
            foreach ($items as $item) {
                // Double-check to ensure a DOI object does not already exist for publication
                if ($item->doi_id === null) {
                    $doiId = $this->_addDoi($item->context_id, $item->setting_value);

                    // Add association to newly created DOI to publication
                    DB::table('submission_chapters')
                        ->where('chapter_id', '=', $item->chapter_id)
                        ->update(['doi_id' => $doiId]);
                } else {
                    // Otherwise, update existing DOI object
                    $this->_updateDoi($item->doi_id, $item->context_id, $item->setting_value);
                }
            }
        }, 'sc.chapter_id', 'chapter_id');

        // Remove pub-id::doi settings entry
        DB::table('publication_settings')
            ->where('setting_name', '=', 'pub-id::doi')
            ->delete();
    }

    /**
     * Move submission file DOIs from submission_file_settings table to DOI objects
     */
    private function _migrateSubmissionFileDoisUp(): void
    {
        $q = DB::table('submissions', 's')
            ->select(['s.context_id', 'sf.submission_file_id', 'sf.doi_id', 'sfss.setting_name', 'sfss.setting_value'])
            ->leftJoin('submission_files as sf', 's.submission_id', '=', 'sf.submission_id')
            ->leftJoin('submission_file_settings as sfss', 'sf.submission_file_id', '=', 'sfss.submission_file_id')
            ->where('sfss.setting_name', '=', 'pub-id::doi');

        $q->chunkById(1000, function ($items) {
            foreach ($items as $item) {
                // Double-check to ensure a DOI object does not already exist for publication
                if ($item->doi_id === null) {
                    $doiId = $this->_addDoi($item->context_id, $item->setting_value);

                    // Add association to newly created DOI to publication
                    DB::table('submission_files')
                        ->where('submission_file_id', '=', $item->submission_file_id)
                        ->update(['doi_id' => $doiId]);
                } else {
                    // Otherwise, update existing DOI object
                    $this->_updateDoi($item->doi_id, $item->context_id, $item->setting_value);
                }
            }
        }, 'sf.submission_file_id', 'submission_file_id');

        // Remove pub-id::doi settings entry
        DB::table('publication_settings')
            ->where('setting_name', '=', 'pub-id::doi')
            ->delete();
    }

    /**
     * Gets app-specific context table name, e.g. journals
     */
    protected function getContextTable(): string
    {
        return 'presses';
    }

    /**
     * Gets app-specific context_id column, e.g. journal_id
     */
    protected function getContextIdColumn(): string
    {
        return 'press_id';
    }

    /**
     * Gets app-specific context settings table, e.g. journal_settings
     */
    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    /**
     * Adds app-specific suffix patterns to data collector stdClass
     */
    protected function addSuffixPatternsData(\stdClass $data): \stdClass
    {
        $data->doiChapterSuffixPattern = [];
        $data->doiSubmissionFileSuffixPattern = [];

        return $data;
    }

    /**
     * Adds suffix pattern settings from DB into reducer's data
     */
    protected function insertSuffixPatternsData(\stdClass $carry, \stdClass $item): \stdClass
    {
        switch ($item->setting_name) {
            case 'doiChapterSuffixPattern':
                $carry->doiChapterSuffixPattern[] = [
                    $this->getContextIdColumn() => $item->context_id,
                    'setting_name' => $item->setting_name,
                    'setting_value' => $item->setting_value
                ];
                return $carry;
            case 'doiSubmissionFileSuffixPattern':
                $carry->doiSubmissionFileSuffixPattern[] = [
                    $this->getContextIdColumn() => $item->context_id,
                    'setting_name' => $item->setting_name,
                    'setting_value' => $item->setting_value
                ];
                return $carry;
            default:
                return $carry;
        }
    }

    /**
     * Adds insert-ready statements for all applicable suffix pattern items
     */
    protected function prepareSuffixPatternsForInsert(\stdClass $processedData, array $insertData): array
    {
        foreach ($processedData->doiChapterSuffixPattern as $item) {
            $insertData[] = $item;
        }
        foreach ($processedData->doiSubmissionFileSuffixPattern as $item) {
            $insertData[] = $item;
        }

        return $insertData;
    }

    /**
     * Add app-specific enabled DOI types for insert into DB
     */
    protected function insertEnabledDoiTypes(\stdClass $carry, \stdClass $item): \stdClass
    {
        $enabledType = null;
        if ($item->setting_name === 'enableChapterDoi') {
            $enabledType = 'chapter';
        } elseif ($item->setting_name === 'enableSubmissionFileDoi') {
            $enabledType = 'file';
        }


        if ($enabledType !== null) {
            if (!isset($carry->enabledDoiTypes[$item->context_id])) {
                $carry->enabledDoiTypes[$item->context_id] = [
                    $this->getContextIdColumn() => $item->context_id,
                    'setting_name' => 'enabledDoiTypes',
                    'setting_value' => [],
                ];
            }

            if ($item->setting_value === '1') {
                $carry->enabledDoiTypes[$item->context_id]['setting_value'][] = $enabledType;
            }
        }

        return $carry;
    }

    /**
     * Get an array with the keys for each suffix pattern type
     */
    protected function getSuffixPatternNames(): array
    {
        return [
            'doiPublicationSuffixPattern',
            'doiRepresentationSuffixPattern',
            'doiChapterSuffixPattern',
            'doiSubmissionFileSuffixPattern',
        ];
    }

    /**
     * Returns the default pattern for the given suffix pattern type
     */
    protected function getSuffixPatternValue(string $suffixPatternName): string
    {
        $pattern = '';
        switch ($suffixPatternName) {
            case 'doiPublicationSuffixPattern':
                $pattern = '%p.%m';
                break;
            case 'doiRepresentationSuffixPattern':
                $pattern = '%p.%m.%f';
                break;
            case 'doiChapterSuffixPattern':
                $pattern = '%p.%m.c%c';
                break;
            case 'doiSubmissionFileSuffixPattern':
                $pattern = '%p.%m.%f.%s';
                break;
        }

        return $pattern;
    }
}
