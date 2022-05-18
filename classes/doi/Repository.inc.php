<?php

/**
 * @file classes/doi/Repository.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class doi
 *
 * @brief A repository to find and manage DOIs.
 */

namespace APP\doi;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\monograph\Chapter;
use APP\plugins\PubIdPlugin;
use APP\press\PressDAO;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use PKP\context\Context;
use PKP\core\DataObject;
use PKP\services\PKPSchemaService;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class Repository extends \PKP\doi\Repository
{
    public const TYPE_SUBMISSION_FILE = 'file';
    public const TYPE_CHAPTER = 'chapter';

    public const CUSTOM_CHAPTER_PATTERN = 'doiChapterSuffixPattern';
    public const CUSTOM_FILE_PATTERN = 'doiFileSuffixPattern';

    public function __construct(DAO $dao, Request $request, PKPSchemaService $schemaService)
    {
        parent::__construct($dao, $request, $schemaService);
    }

    /**
     * Create a DOI for the given publication
     */
    public function mintPublicationDoi(Publication $publication, Submission $submission, Context $context): ?int
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
    public function mintChapterDoi(Chapter $chapter, Submission $submission, Context $context): ?int
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
    public function mintPublicationFormatDoi(PublicationFormat $publicationFormat, Submission $submission, Context $context): ?int
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
    public function mintSubmissionFileDoi(SubmissionFile $submissionFile, Submission $submission, Context $context): ?int
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
        $doiIds = [];

        $submission = Repo::submission()->get($submissionId);
        /** @var Publication[] $publications */
        $publications = [$submission->getCurrentPublication()];

        /** @var PressDAO $contextDao */
        $contextDao = Application::getContextDAO();
        $context = $contextDao->getById($submission->getData('contextId'));

        foreach ($publications as $publication) {
            $publicationDoiId = $publication->getData('doiId');
            if (!empty($publicationDoiId) && $context->isDoiTypeEnabled(self::TYPE_PUBLICATION)) {
                $doiIds[] = $publicationDoiId;
            }

            // Chapters
            $chapters = $publication->getData('chapters');
            foreach ($chapters as $chapter) {
                $chapterDoiId = $chapter->getData('doiId');
                if (!empty($chapterDoiId) && $context->isDoiTypeEnabled(self::TYPE_CHAPTER)) {
                    $doiIds[] = $chapterDoiId;
                }
            }

            // Publication formats
            $publicationFormats = $publication->getData('publicationFormats');
            foreach ($publicationFormats as $publicationFormat) {
                $publicationFormatDoiId
                    = $publicationFormat->getData('doiId');
                if (!empty($publicationFormatDoiId) && $context->isDoiTypeEnabled(self::TYPE_REPRESENTATION)) {
                    $doiIds[] = $publicationFormatDoiId;
                }
            }

            // Submission files
            if ($context->isDoiTypeEnabled(self::TYPE_SUBMISSION_FILE)) {
                $submissionFilesCollector = Repo::submissionFile()
                    ->getCollector()
                    ->filterBySubmissionIds([$publication->getData('submissionId')])
                    ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF]);
                $submissionFiles = Repo::submissionFile()->getMany($submissionFilesCollector);
                /** @var SubmissionFile $submissionFile */
                foreach ($submissionFiles as $submissionFile) {
                    $submissionFileDoiId = $submissionFile->getData('doiId');
                    if (!empty($submissionFileDoiId)) {
                        $doiIds[] = $submissionFileDoiId;
                    }
                }
            }
        }

        return $doiIds;
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
}
