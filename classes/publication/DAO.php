<?php

/**
 * @file classes/publication/DAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DAO
 *
 * @brief Read and write publications to the database.
 */

namespace APP\publication;

use APP\core\Application;
use APP\monograph\ChapterDAO;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PKP\core\interfaces\CollectorInterface;
use PKP\db\DAORegistry;
use PKP\submissionFile\SubmissionFile;

class DAO extends \PKP\publication\DAO
{
    /** @copydoc EntityDAO::$primaryTableColumns */
    public $primaryTableColumns = [
        'id' => 'publication_id',
        'accessStatus' => 'access_status',
        'datePublished' => 'date_published',
        'lastModified' => 'last_modified',
        'locale' => 'locale',
        'primaryContactId' => 'primary_contact_id',
        'seq' => 'seq',
        'seriesId' => 'series_id',
        'submissionId' => 'submission_id',
        'status' => 'status',
        'urlPath' => 'url_path',
        'seriesPosition' => 'series_position',
        'doiId' => 'doi_id',
        'versionStage' => 'version_stage',
        'versionMinor' => 'version_minor',
        'versionMajor' => 'version_major',
        'updateType' => 'update_type',
        'createdAt' => 'created_at',
        'sourcePublicationId' => 'source_publication_id'
    ];

    /**
     * @copydoc SchemaDAO::fromRow()
     */
    public function fromRow(object $row, array $ids, object $cache, ?CollectorInterface $query = null): Publication
    {
        $publication = parent::fromRow($row, $ids, $cache, $query);
        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO */
        $publication->setData('publicationFormats', Application::getRepresentationDao()->getByPublicationId($publication->getId()));
        $publication->setData('chapters', $chapterDao->getByPublicationId($publication->getId())->toArray());
        return $publication;
    }

    /**
     * @copydoc \PKP\publication\DAO::whereHasDoi()
     */
    protected function whereHasDoi(Builder $q): Builder
    {
        return parent::whereHasDoi($q)
            ->orWhereExists(
                fn (Builder $q) => $q->select(DB::raw(1))
                    ->from('submission_chapters as spc')
                    ->whereColumn('spc.publication_id', '=', 'p.publication_id')
                    ->whereNotNull('spc.doi_id')
            )
            ->orWhereExists(
                fn (Builder $q) => $q->select(DB::raw(1))
                    ->from('publication_formats as pf')
                    ->whereColumn('pf.publication_id', '=', 'p.publication_id')
                    ->where(
                        fn (Builder $q) => $q->whereNotNull('pf.doi_id')
                            ->orWhereExists(
                                fn (Builder $q) => $q->select(DB::raw(1))
                                    ->from('submission_files as sf')
                                    ->where('sf.assoc_type', '=', Application::ASSOC_TYPE_PUBLICATION_FORMAT)
                                    ->whereColumn('sf.assoc_id', '=', 'pf.publication_format_id')
                                    ->where('sf.file_stage', '=', SubmissionFile::SUBMISSION_FILE_PROOF)
                                    ->whereNotNull('sf.doi_id')
                            )
                    )
            );
    }
}
