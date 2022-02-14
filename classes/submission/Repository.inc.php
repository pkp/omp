<?php
/**
 * @file classes/submission/Repository.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submission
 *
 * @brief A repository to find and manage submissions.
 */

namespace APP\submission;

use APP\core\Application;
use APP\facades\Repo;
use APP\monograph\Chapter;
use APP\monograph\ChapterDAO;
use APP\press\Press;
use APP\press\PressDAO;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\PublicationFormatDAO;
use PKP\submissionFile\SubmissionFile;

class Repository extends \PKP\submission\Repository
{
    /** @copydoc \PKP\submission\Repository::$schemaMap */
    public $schemaMap = maps\Schema::class;

    /** @copydoc \PKP\submission\Repository::getSortSelectOptions() */
    public function getSortSelectOptions(): array
    {
        return array_merge(
            parent::getSortSelectOptions(),
            [
                $this->getSortOption(Collector::ORDERBY_SERIES_POSITION, Collector::ORDER_DIR_ASC) => __('catalog.sortBy.seriesPositionAsc'),
                $this->getSortOption(Collector::ORDERBY_SERIES_POSITION, Collector::ORDER_DIR_DESC) => __('catalog.sortBy.seriesPositionDesc'),
            ]
        );
    }

    /**
     * Creates and assigns DOIs to all sub-objects if:
     * 1) the suffix pattern can currently be created, and
     * 2) it does not already exist.
     *
     */
    public function createDois(Submission $submission): void
    {
        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();

        /** @var Press $context */
        $context = $contextDao->getById($submission->getData('contextId'));

        $publication = $submission->getCurrentPublication();
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_PUBLICATION) && empty($publication->getData('doiId'))) {
            $doiId = Repo::doi()->mintPublicationDoi($publication, $submission, $context);
            if ($doiId != null) {
                Repo::publication()->edit($publication, ['doiId' => $doiId]);
            }
        }
        // Chapters
        /** @var Chapter[] $chapters */
        $chapters = $publication->getData('chapters');
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_CHAPTER) && !empty($chapters)) {
            /** @var ChapterDAO $chapterDao */
            $chapterDao = \DAORegistry::getDAO('ChapterDAO');
            foreach ($chapters as $chapter) {
                if (!empty($chapter->getData('doiId'))) {
                    continue;
                }

                $doiId = Repo::doi()->mintChapterDoi($chapter, $submission, $context);
                if ($doiId != null) {
                    $chapter->setData('doiId', $doiId);
                    $chapterDao->updateObject($chapter);
                }
            }
        }

        // Publication formats
        $publicationFormats = $publication->getData('publicationFormats');
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_REPRESENTATION) && !empty($publicationFormats)) {
            /** @var PublicationFormatDAO $publicationFormatDao */
            $publicationFormatDao = \DAORegistry::getDAO('PublicationFormatDAO');
            /** @var PublicationFormat $publicationFormat */
            foreach ($publicationFormats as $publicationFormat) {
                if (!empty($publicationFormat->getData('doiId'))) {
                    continue;
                }

                $doiId = Repo::doi()->mintPublicationFormatDoi($publicationFormat, $submission, $context);
                if ($doiId != null) {
                    $publicationFormat->setData('doiId', $doiId);
                    $publicationFormatDao->updateObject($publicationFormat);
                }
            }
        }

        // Submission files
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_SUBMISSION_FILE)) {
            // Get all submission files assigned to a publication format
            $submissionFilesCollector = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$publication->getData('submissionId')])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF]);

            $submissionFiles = Repo::submissionFile()->getMany($submissionFilesCollector);
            /** @var SubmissionFile $submissionFile */
            foreach ($submissionFiles as $submissionFile) {
                if (!empty($submissionFile->getData('doiId'))) {
                    continue;
                }

                $doiId = Repo::doi()->mintSubmissionFileDoi($submissionFile, $submission, $context);
                if ($doiId != null) {
                    Repo::submissionFile()->edit($submissionFile, ['doiId' => $doiId]);
                }
            }
        }
    }
}
