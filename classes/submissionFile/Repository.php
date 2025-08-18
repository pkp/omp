<?php

/**
 * @file classes/submissionFile/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage submission files.
 */

namespace APP\submissionFile;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\submissionFile\maps\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PKP\plugins\Hook;
use PKP\services\PKPSchemaService;
use PKP\submissionFile\Collector;
use PKP\submissionFile\Repository as SubmissionFileRepository;
use PKP\submissionFile\SubmissionFile;

class Repository extends SubmissionFileRepository
{
    public string $schemaMap = Schema::class;

    public array $reviewFileStages = [
        SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
        SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
        SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT,
        SubmissionFile::SUBMISSION_FILE_REVIEW_FILE,
        SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION,
    ];

    public function __construct(
        DAO $dao,
        Request $request,
        PKPSchemaService $schemaService
    ) {
        $this->schemaService = $schemaService;
        $this->dao = $dao;
        $this->request = $request;
    }

    public function getCollector(): Collector
    {
        return App::makeWith(Collector::class, ['dao' => $this->dao]);
    }

    public function getFileStages(): array
    {
        $stages = [
            SubmissionFile::SUBMISSION_FILE_SUBMISSION,
            SubmissionFile::SUBMISSION_FILE_NOTE,
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
            SubmissionFile::SUBMISSION_FILE_REVIEW_FILE,
            SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT,
            SubmissionFile::SUBMISSION_FILE_FINAL,
            SubmissionFile::SUBMISSION_FILE_COPYEDIT,
            SubmissionFile::SUBMISSION_FILE_PROOF,
            SubmissionFile::SUBMISSION_FILE_PRODUCTION_READY,
            SubmissionFile::SUBMISSION_FILE_ATTACHMENT,
            SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION,
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
            SubmissionFile::SUBMISSION_FILE_DEPENDENT,
            SubmissionFile::SUBMISSION_FILE_QUERY,
        ];

        Hook::call('SubmissionFile::fileStages', [&$stages]);

        return $stages;
    }

    /**
     * Get submission files of all minor versions of the same submission, that are
     * with the same version stage, version major and DOI ID
     * as the given submission file.
     *
     * @return array<int,T>
     */
    public function getMinorVersionsWithSameDoi(SubmissionFile $submissionFile): array
    {
        if ($submissionFile->getData('assocType') != Application::ASSOC_TYPE_PUBLICATION_FORMAT) {
            return [];
        }
        $publicationFormatId = $submissionFile->getData('assocId');
        $publicationId = DB::table('publication_formats')
            ->where('publication_format_id', '=', $publicationFormatId)
            ->select('publication_id')
            ->first()?->publication_id;
        $publication = Repo::publication()->get($publicationId);
        if (!$publication) {
            return [];
        }

        $allMinorVersionIds = Repo::publication()->getCollector()
            ->filterBySubmissionIds([$publication->getData('submissionId')])
            ->filterByVersionStage($publication->getData('versionStage'))
            ->filterByVersionMajor($publication->getData('versionMajor'))
            ->getIds()
            ->values()
            ->toArray();
        $publicationFormatIds = DB::table('publication_formats')
            ->whereIn('publication_id', $allMinorVersionIds)
            ->select('publication_format_id')
            ->pluck('publication_format_id')
            ->toArray();
        return $this->getCollector()
            ->filterBySubmissionIds([$submissionFile->getData('submissionId')])
            ->filterByAssoc(Application::ASSOC_TYPE_PUBLICATION_FORMAT, $publicationFormatIds)
            ->filterByDoiIds([$submissionFile->getData('doiId')])
            ->getMany()
            ->all();
    }
}
