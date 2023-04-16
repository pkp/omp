<?php
/**
 * @file classes/submissionFile/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submissionFile
 *
 * @brief A repository to find and manage submission files.
 */

namespace APP\submissionFile;

use APP\core\Request;
use APP\submissionFile\maps\Schema;
use Illuminate\Support\Facades\App;
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
}
