<?php
/**
 * @file classes/submission/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
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
use Illuminate\Support\Collection;
use PKP\context\Context;
use PKP\db\DAORegistry;
use PKP\doi\exceptions\DoiException;
use PKP\security\Role;
use PKP\submission\DashboardView;
use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;
use PKP\user\User;

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
    public function createDois(Submission $submission): array
    {
        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();

        /** @var Press $context */
        $context = $contextDao->getById($submission->getData('contextId'));

        $publication = $submission->getCurrentPublication();

        $doiCreationFailures = [];

        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_PUBLICATION) && empty($publication->getData('doiId'))) {
            try {
                $doiId = Repo::doi()->mintPublicationDoi($publication, $submission, $context);
                Repo::publication()->edit($publication, ['doiId' => $doiId]);
            } catch (DoiException $exception) {
                $doiCreationFailures[] = $exception;
            }
        }

        // Chapters
        /** @var Chapter[] $chapters */
        $chapters = $publication->getData('chapters');
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_CHAPTER) && !empty($chapters)) {
            /** @var ChapterDAO $chapterDao */
            $chapterDao = DAORegistry::getDAO('ChapterDAO');
            foreach ($chapters as $chapter) {
                if (empty($chapter->getData('doiId')) && $chapter->isPageEnabled()) {
                    try {
                        $doiId = Repo::doi()->mintChapterDoi($chapter, $submission, $context);
                        $chapter->setData('doiId', $doiId);
                        $chapterDao->updateObject($chapter);
                    } catch (DoiException $exception) {
                        $doiCreationFailures[] = $exception;
                    }
                }
            }
        }

        // Publication formats
        $publicationFormats = $publication->getData('publicationFormats');
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_REPRESENTATION) && !empty($publicationFormats)) {
            /** @var PublicationFormatDAO $publicationFormatDao */
            $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
            /** @var PublicationFormat $publicationFormat */
            foreach ($publicationFormats as $publicationFormat) {
                if (empty($publicationFormat->getData('doiId'))) {
                    try {
                        $doiId = Repo::doi()->mintPublicationFormatDoi($publicationFormat, $submission, $context);
                        $publicationFormat->setData('doiId', $doiId);
                        $publicationFormatDao->updateObject($publicationFormat);
                    } catch (DoiException $exception) {
                        $doiCreationFailures[] = $exception;
                    }
                }
            }
        }

        // Submission files
        if ($context->isDoiTypeEnabled(Repo::doi()::TYPE_SUBMISSION_FILE)) {
            // Get all submission files assigned to a publication format
            $submissionFiles = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$publication->getData('submissionId')])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
                ->getMany();

            /** @var SubmissionFile $submissionFile */
            foreach ($submissionFiles as $submissionFile) {
                if (empty($submissionFile->getData('doiId'))) {
                    try {
                        $doiId = Repo::doi()->mintSubmissionFileDoi($submissionFile, $submission, $context);
                        Repo::submissionFile()->edit($submissionFile, ['doiId' => $doiId]);
                    } catch (DoiException $exception) {
                        $doiCreationFailures[] = $exception;
                    }
                }
            }
        }

        return $doiCreationFailures;
    }

    protected function mapDashboardViews($types, Context $context, User $user, bool $canAccessUnassignedSubmission): Collection
    {
        $views = parent::mapDashboardViews($types, $context, $user, $canAccessUnassignedSubmission);

        $collector = Repo::submission()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterByStageIds([WORKFLOW_STAGE_ID_INTERNAL_REVIEW])
            ->filterByStatus([PKPSubmission::STATUS_QUEUED]);

        return $views->put(DashboardView::TYPE_REVIEW_INTERNAL, new DashboardView(
            DashboardView::TYPE_REVIEW_INTERNAL,
            __('submission.dashboard.view.reviewInternal'),
            [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT],
            $canAccessUnassignedSubmission ? $collector : $collector->assignedTo([$user->getId()]),
            $canAccessUnassignedSubmission ? null : 'assigned',
            ['stageIds' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW], 'status' => [PKPSubmission::STATUS_QUEUED]]
        ));
    }
}
