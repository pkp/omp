<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7014_DoiMigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7014_DoiMigration
 * @brief Describe upgrade/downgrade operations for DB table dois.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\install\DoiMigration;
use PKP\migration\Migration;

class I7014_DoiMigration extends Migration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        // DOIs
        $doiMigrations = new DoiMigration($this->_installer, $this->_attributes);
        $doiMigrations->up();

        // Context ID foreign key needs to be added on an app-specific basis
        Schema::table('dois', function (Blueprint $table) {
            $table->foreign('context_id')->references('press_id')->on('presses');
        });

        // Add doiId to publication
        Schema::table('publications', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Add doiId to chapters
        Schema::table('submission_chapters', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Add doiId to publication formats
        Schema::table('publication_formats', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
        });

        // Add doiId to submission files
        Schema::table('submission_files', function (Blueprint $table) {
            $table->bigInteger('doi_id')->nullable();
            $table->foreign('doi_id')->references('doi_id')->on('dois')->nullOnDelete();
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

    private function migrateExistingDataUp()
    {
        // Find all existing DOIs, move to new DOI objects and add foreign key for pub object
        $this->_migrateDoiSettingsToContext();
        $this->_migratePublicationDoisUp();
        $this->_migrateRepresentationDoisUp();
        $this->_migrateChapterDoisUp();
        $this->_migrateSubmissionFileDoisUp();
    }

    /**
     * Move DOI settings from plugin_settings to Context (Press) settings
     */
    private function _migrateDoiSettingsToContext()
    {
        // Get plugin_based settings
        $q = DB::table('plugin_settings')
            ->where('plugin_name', '=', 'doipubidplugin')
            ->select(['context_id','setting_name', 'setting_value']);
        $results = $q->get();

        $data = new \stdClass();
        $data->enabledDois = [];
        $data->doiCreationTime = [];
        $data->enabledDoiTypes = [];
        $data->doiPrefix = [];
        $data->customDoiSuffixType = [];
        $data->doiPublicationSuffixPattern = [];
        $data->doiRepresentationSuffixPattern = [];
        $data->doiChapterSuffixPattern = [];
        $data->doiSubmissionFileSuffixPattern = [];

        // Map to context-based settings
        $results->reduce(function ($carry, $item) {
            switch ($item->setting_name) {
                case 'enabled':
                    $carry->enabledDois[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => 'enableDois',
                        'setting_value' => (int) $item->setting_value,
                    ];
                    $carry->doiCreationTime[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => 'doiCreationTime',
                        'setting_value' => 'copyEditCreationTime',
                    ];
                    return $carry;
                case 'enablePublicationDoi':
                    if (!isset($carry->enabledDoiTypes[$item->context_id])) {
                        $carry->enabledDoiTypes[$item->context_id] = [
                            'press_id' => $item->context_id,
                            'setting_name' => 'enabledDoiTypes',
                            'setting_value' => [],
                        ];
                    }

                    if ($item->setting_value === '1') {
                        array_push($carry->enabledDoiTypes[$item->context_id]['setting_value'], 'publication');
                    }
                    return $carry;
                case 'enableRepresentationDoi':
                    if (!isset($carry->enabledDoiTypes[$item->context_id])) {
                        $carry->enabledDoiTypes[$item->context_id] = [
                            'press_id' => $item->context_id,
                            'setting_name' => 'enabledDoiTypes',
                            'setting_value' => [],
                        ];
                    }

                    if ($item->setting_value === '1') {
                        array_push($carry->enabledDoiTypes[$item->context_id]['setting_value'], 'representation');
                    }
                    return $carry;
                case 'enableChapterDoi':
                    if (!isset($carry->enabledDoiTypes[$item->context_id])) {
                        $carry->enabledDoiTypes[$item->context_id] = [
                            'press_id' => $item->context_id,
                            'setting_name' => 'enabledDoiTypes',
                            'setting_value' => [],
                        ];
                    }

                    if ($item->setting_value === '1') {
                        array_push($carry->enabledDoiTypes[$item->context_id]['setting_value'], 'chapter');
                    }
                    return $carry;
                case 'enableSubmissionFileDoi':
                    if (!isset($carry->enabledDoiTypes[$item->context_id])) {
                        $carry->enabledDoiTypes[$item->context_id] = [
                            'press_id' => $item->context_id,
                            'setting_name' => 'enabledDoiTypes',
                            'setting_value' => [],
                        ];
                    }

                    if ($item->setting_value === '1') {
                        array_push($carry->enabledDoiTypes[$item->context_id]['setting_value'], 'file');
                    }
                    return $carry;
                case 'doiSuffix':
                    $value = '';
                    switch ($item->setting_value) {
                        case 'default':
                            $value = 'defaultPattern';
                            break;
                        case 'pattern':
                            $value = 'customPattern';
                            break;
                        case 'customId':
                            $value = 'customId';
                            break;
                    }
                    $carry->customDoiSuffixType[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => 'customDoiSuffixType',
                        'setting_value' => $value,
                    ];
                    return $carry;
                case 'doiPrefix':
                    $carry->doiPrefix[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => $item->setting_name,
                        'setting_value' => $item->setting_value,
                    ];
                    return $carry;
                case 'doiPublicationSuffixPattern':
                    $carry->doiPublicationSuffixPattern[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => $item->setting_name,
                        'setting_value' => $item->setting_value,
                    ];
                    return $carry;
                case 'doiRepresentationSuffixPattern':
                    $carry->doiRepresentationSuffixPattern[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => $item->setting_name,
                        'setting_value' => $item->setting_value,
                    ];
                    return $carry;
                case 'doiChapterSuffixPattern':
                    $carry->doiChapterSuffixPattern[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => $item->setting_name,
                        'setting_value' => $item->setting_value,
                    ];
                    return $carry;
                case 'doiSubmissionFileSuffixPattern':
                    $carry->doiSubmissionFileSuffixPattern[] = [
                        'press_id' => $item->context_id,
                        'setting_name' => $item->setting_name,
                        'setting_value' => $item->setting_value,
                    ];
                    return $carry;
                default:
                    return $carry;
            }
        }, $data);

        // Prepare insert statements
        $insertData = [];
        foreach ($data->enabledDois as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->doiCreationTime as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->enabledDoiTypes as $item) {
            $item['setting_value'] = json_encode($item['setting_value']);
            array_push($insertData, $item);
        }
        foreach ($data->doiPrefix as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->customDoiSuffixType as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->doiPublicationSuffixPattern as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->doiRepresentationSuffixPattern as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->doiChapterSuffixPattern as $item) {
            array_push($insertData, $item);
        }
        foreach ($data->doiSubmissionFileSuffixPattern as $item) {
            array_push($insertData, $item);
        }

        DB::table('press_settings')->insert($insertData);

        // Add minimum required DOI settings to context if DOI plugin not previously enabled
        $missingDoiSettingsInsertStatement = DB::table('presses')
            ->select('press_id')
            ->whereNotIn('press_id', function (Builder $q) {
                $q->select('press_id')
                    ->from('press_settings')
                    ->where('setting_name', '=', 'enableDois');
            })
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[] = [
                    'press_id' => $item->press_id,
                    'setting_name' => 'enableDois',
                    'setting_value' => 0,
                ];
                $carry[] = [
                    'press_id' => $item->press_id,
                    'setting_name' => 'doiCreationTime',
                    'setting_value' => 'copyEditCreationTime'
                ];
                return $carry;
            }, []);

        DB::table('press_settings')->insert($missingDoiSettingsInsertStatement);

        // Cleanup old DOI plugin settings
        DB::table('plugin_settings')
            ->where('plugin_name', '=', 'doipubidplugin')
            ->delete();
        DB::table('versions')
            ->where('product_type', '=', 'plugins.pubIds')
            ->where('product', '=', 'doi')
            ->update(['current' => 0]);
    }

    /**
     * Move publication DOIs from publication_settings table to DOI objects
     */
    private function _migratePublicationDoisUp(): void
    {
        $q = DB::table('submissions', 's')
            ->select(['s.context_id', 'p.publication_id', 'p.doi_id', 'pss.setting_name', 'pss.setting_value'])
            ->leftJoin('publications as p', 'p.submission_id', '=', 's.submission_id')
            ->leftJoin('publication_settings as pss', 'pss.publication_id', '=', 'p.publication_id')
            ->where('pss.setting_name', '=', 'pub-id::doi');

        $q->chunkById(1000, function ($items) {
            foreach ($items as $item) {
                // Double-check to ensure a DOI object does not already exist for publication
                if ($item->doi_id === null) {
                    $doiId = $this->_addDoi($item->context_id, $item->setting_value);

                    // Add association to newly created DOI to publication
                    DB::table('publications')
                        ->where('publication_id', '=', $item->publication_id)
                        ->update(['doi_id' => $doiId]);
                } else {
                    // Otherwise, update existing DOI object
                    $this->_updateDoi($item->doi_id, $item->context_id, $item->setting_value);
                }
            }
        }, 'publication_id');

        // Remove pub-id::doi settings entry
        DB::table('publication_settings')
            ->where('setting_name', '=', 'pub-id::doi')
            ->delete();
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
        }, 'publication_format_id');

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
        }, 'chapter_id');

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
        }, 'submission_file_id');

        // Remove pub-id::doi settings entry
        DB::table('publication_settings')
            ->where('setting_name', '=', 'pub-id::doi')
            ->delete();
    }

    /**
     * Creates a new DOI object for a given context ID and DOI
     *
     */
    private function _addDoi(string $contextId, string $doi): int
    {
        return DB::table('dois')
            ->insertGetId(
                [
                    'context_id' => $contextId,
                    'doi' => $doi,
                ]
            );
    }

    /**
     * Update the context ID and doi for a given DOI object
     *
     */
    private function _updateDoi(int $doiId, string $contextId, string $doi): int
    {
        return DB::table('dois')
            ->where('doi_id', '=', $doiId)
            ->update(
                [
                    'context_id' => $contextId,
                    'doi' => $doi
                ]
            );
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\migration\upgrade\v3_4_0\I7014_DoiMigration', '\I7014_DoiMigration');
}
