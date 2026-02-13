<?php

/**
 * @file classes/doi/Repository.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage DOIs.
 */

namespace APP\doi;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\monograph\Chapter;
use APP\monograph\ChapterDAO;
use APP\plugins\PubIdPlugin;
use APP\press\Press;
use APP\press\PressDAO;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use PKP\context\Context;
use PKP\core\DataObject;
use PKP\db\DAORegistry;
use PKP\doi\Collector;
use PKP\plugins\Hook;
use PKP\services\PKPSchemaService;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class Repository extends \PKP\doi\Repository
{
    public const TYPE_SUBMISSION_FILE = 'file';
    public const TYPE_CHAPTER = 'chapter';
    public const TYPE_PEER_REVIEW = 'peerReview';
    public const TYPE_AUTHOR_RESPONSE = 'authorResponse';

    public const CUSTOM_CHAPTER_PATTERN = 'doiChapterSuffixPattern';
    public const CUSTOM_FILE_PATTERN = 'doiSubmissionFileSuffixPattern';

    public function __construct(DAO $dao, Request $request, PKPSchemaService $schemaService)
    {
        parent::__construct($dao, $request, $schemaService);
    }

    public function getCollector(): Collector
    {
        return App::makeWith(Collector::class, ['dao' => $this->dao]);
    }

    /**
     * Create a DOI for the given publication
     */
    public function mintPublicationDoi(Publication $publication, Submission $submission, Context $context): int
    {
        if ($context->getData(Context::SETTING_DOI_SUFFIX_TYPE) === Repo::doi()::SUFFIX_DEFAULT) {
            $doiSuffix = $this->generateDefaultSuffix();
        } else {
            $doiSuffix = $this->generateSuffixPattern($publication, $context, $context->getData(Context::SETTING_DOI_SUFFIX_TYPE), $submission);
        }

        return $this->mintAndStoreDoi($context, $doiSuffix);
    }

    /**
     * Create a DOI for the given chapter
     */
    public function mintChapterDoi(Chapter $chapter, Submission $submission, Context $context): int
    {
        if ($context->getData(Context::SETTING_DOI_SUFFIX_TYPE) === Repo::doi()::SUFFIX_DEFAULT) {
            $doiSuffix = $this->generateDefaultSuffix();
        } else {
            $doiSuffix = $this->generateSuffixPattern($chapter, $context, $context->getData(Context::SETTING_DOI_SUFFIX_TYPE), $submission, $chapter);
        }

        return $this->mintAndStoreDoi($context, $doiSuffix);
    }

    /**
     * Create a DOI for the given publication format
     */
    public function mintPublicationFormatDoi(PublicationFormat $publicationFormat, Submission $submission, Context $context): int
    {
        if ($context->getData(Context::SETTING_DOI_SUFFIX_TYPE) === Repo::doi()::SUFFIX_DEFAULT) {
            $doiSuffix = $this->generateDefaultSuffix();
        } else {
            $doiSuffix = $this->generateSuffixPattern($publicationFormat, $context, $context->getData(Context::SETTING_DOI_SUFFIX_TYPE), $submission, null, $publicationFormat);
        }

        return $this->mintAndStoreDoi($context, $doiSuffix);
    }

    /**
     * Create a DOI for the given submission file
     */
    public function mintSubmissionFileDoi(SubmissionFile $submissionFile, Submission $submission, Context $context): int
    {
        if ($context->getData(Context::SETTING_DOI_SUFFIX_TYPE) === Repo::doi()::SUFFIX_DEFAULT) {
            $doiSuffix = $this->generateDefaultSuffix();
        } else {
            $doiSuffix = $this->generateSuffixPattern($submissionFile, $context, $context->getData(Context::SETTING_DOI_SUFFIX_TYPE), $submission, null, null, $submissionFile);
        }

        return $this->mintAndStoreDoi($context, $doiSuffix);
    }

    public function getDoisForSubmission(int $submissionId): array
    {
        $doiIds = Collection::make();

        $submission = Repo::submission()->get($submissionId);
        /** @var Publication[] $publications */
        $publications = [$submission->getCurrentPublication()];

        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();
        /** @var Press */
        $context = $contextDao->getById($submission->getData('contextId'));

        foreach ($publications as $publication) {
            $publicationDoiId = $publication->getData('doiId');
            if (!empty($publicationDoiId) && $context->isDoiTypeEnabled(self::TYPE_PUBLICATION)) {
                $doiIds->add($publicationDoiId);
            }

            // Chapters
            $chapters = $publication->getData('chapters');
            foreach ($chapters as $chapter) {
                $chapterDoiId = $chapter->getData('doiId');
                if (!empty($chapterDoiId) && $context->isDoiTypeEnabled(self::TYPE_CHAPTER)) {
                    $doiIds->add($chapterDoiId);
                }
            }

            // Publication formats
            $publicationFormats = $publication->getData('publicationFormats');
            foreach ($publicationFormats as $publicationFormat) {
                $publicationFormatDoiId
                    = $publicationFormat->getData('doiId');
                if (!empty($publicationFormatDoiId) && $context->isDoiTypeEnabled(self::TYPE_REPRESENTATION)) {
                    $doiIds->add($publicationFormatDoiId);
                }
            }

            // Submission files
            if ($context->isDoiTypeEnabled(self::TYPE_SUBMISSION_FILE)) {
                $submissionFiles = Repo::submissionFile()
                    ->getCollector()
                    ->filterBySubmissionIds([$publication->getData('submissionId')])
                    ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
                    ->getMany();

                /** @var SubmissionFile $submissionFile */
                foreach ($submissionFiles as $submissionFile) {
                    $submissionFileDoiId = $submissionFile->getData('doiId');
                    if (!empty($submissionFileDoiId)) {
                        $doiIds->add($submissionFileDoiId);
                    }
                }
            }
        }

        return $doiIds->unique()->toArray();
    }

    /**
     * Gets all DOI IDs related to a publication
     *
     * @return array<int> DOI IDs
     */
    public function getDoisForPublication(Publication $publication): array
    {
        $doiIds = Collection::make();

        $submission = Repo::submission()->get($publication->getData('submissionId'));

        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();
        /** @var Press */
        $context = $contextDao->getById($submission->getData('contextId'));

        $publicationDoiId = $publication->getData('doiId');
        if (!empty($publicationDoiId) && $context->isDoiTypeEnabled(self::TYPE_PUBLICATION)) {
            $doiIds->add($publicationDoiId);
        }

        // Chapters
        $chapters = $publication->getData('chapters');
        foreach ($chapters as $chapter) {
            $chapterDoiId = $chapter->getData('doiId');
            if (!empty($chapterDoiId) && $context->isDoiTypeEnabled(self::TYPE_CHAPTER)) {
                $doiIds->add($chapterDoiId);
            }
        }

        // Publication formats
        $publicationFormats = $publication->getData('publicationFormats');
        foreach ($publicationFormats as $publicationFormat) {
            $publicationFormatDoiId
                = $publicationFormat->getData('doiId');
            if (!empty($publicationFormatDoiId) && $context->isDoiTypeEnabled(self::TYPE_REPRESENTATION)) {
                $doiIds->add($publicationFormatDoiId);
            }
        }

        // Submission files
        if ($context->isDoiTypeEnabled(self::TYPE_SUBMISSION_FILE)) {
            $submissionFiles = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$publication->getData('submissionId')])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
                ->getMany();

            /** @var SubmissionFile $submissionFile */
            foreach ($submissionFiles as $submissionFile) {
                $submissionFileDoiId = $submissionFile->getData('doiId');
                if (!empty($submissionFileDoiId)) {
                    $doiIds->add($submissionFileDoiId);
                }
            }
        }

        return $doiIds->unique()->toArray();
    }

    /**
     * Generate a suffix using a provided pattern type
     *
     * @param string $patternType Repo::doi()::CUSTOM_SUFFIX_* constants
     *
     */
    protected function generateSuffixPattern(
        DataObject $object,
        Context $context,
        string $patternType,
        ?Submission $submission = null,
        ?Chapter $chapter = null,
        ?Representation $representation = null,
        ?SubmissionFile $submissionFile = null
    ): string {
        $doiSuffix = '';
        switch ($patternType) {
            case self::SUFFIX_CUSTOM_PATTERN:
                $pubIdSuffixPattern = $this->getPubIdSuffixPattern($object, $context);
                $doiSuffix = PubIdPlugin::generateCustomPattern($context, $pubIdSuffixPattern, $object, $submission, $chapter, $representation, $submissionFile);
                break;
            case self::SUFFIX_MANUAL:
                break;
        }

        return $doiSuffix;
    }

    /**
     * Gets legacy, user-generated suffix pattern associated with object type and context
     *
     */
    private function getPubIdSuffixPattern(DataObject $object, Context $context): ?string
    {
        if ($object instanceof SubmissionFile) {
            return $context->getData(Repo::doi()::CUSTOM_FILE_PATTERN);
        } elseif ($object instanceof Representation) {
            return $context->getData(Repo::doi()::CUSTOM_REPRESENTATION_PATTERN);
        } elseif ($object instanceof Chapter) {
            return $context->getData(Repo::doi()::CUSTOM_CHAPTER_PATTERN);
        } else {
            return $context->getData(Repo::doi()::CUSTOM_PUBLICATION_PATTERN);
        }
    }

    /**
     * Get app-specific DOI type constants to check when scheduling deposit for submissions
     *
     */
    protected function getValidSubmissionDoiTypes(): array
    {
        return [
            self::TYPE_PUBLICATION,
            self::TYPE_CHAPTER,
            self::TYPE_REPRESENTATION,
            self::TYPE_SUBMISSION_FILE
        ];
    }

    /**
     * Checks whether a DOI object is referenced by ID on any pub objects for a given pub object type.
     *
     * @param string $pubObjectType One of Repo::doi()::TYPE_* constants
     */
    public function isAssigned(int $doiId, string $pubObjectType): bool
    {
        $getChapterCount = function () use ($doiId) {
            /** @var ChapterDAO $chapterDao */
            $chapterDao = DAORegistry::getDAO('ChapterDAO');
            return count($chapterDao->getByDoiId($doiId)->toArray());
        };

        $getPublicationFormatCount = function () use ($doiId) {
            /** @var PublicationFormatDAO $publicationFormatDao */
            $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
            return count($publicationFormatDao->getByDoiId($doiId)->toArray());
        };

        $getSubmissionFilesCount = function () use ($doiId) {
            Hook::add('SubmissionFile::Collector::getQueryBuilder', function ($hookName, $args) use ($doiId) {
                /** @var Builder $qb */
                $qb = & $args[0];
                $qb->when($doiId !== null, function (Builder $qb) use ($doiId) {
                    $qb->where('sf.doi_id', '=', $doiId);
                });
            });

            return Repo::submissionFile()
                ->getCollector()
                ->getQueryBuilder()
                ->getCountForPagination();
        };

        $isAssigned = match ($pubObjectType) {
            Repo::doi()::TYPE_CHAPTER => $getChapterCount(),
            Repo::doi()::TYPE_REPRESENTATION => $getPublicationFormatCount(),
            Repo::doi()::TYPE_SUBMISSION_FILE => $getSubmissionFilesCount(),
            default => false,
        };



        return $isAssigned || parent::isAssigned($doiId, $pubObjectType);
    }
}
