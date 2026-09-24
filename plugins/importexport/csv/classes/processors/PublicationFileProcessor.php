<?php

/**
 * @file plugins/importexport/csv/classes/processors/PublicationFileProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFileProcessor
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publication files.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\core\Application;
use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\plugins\importexport\csv\classes\caches\CachedEntities;
use PKP\core\Core;
use PKP\core\PKPString;
use PKP\submissionFile\SubmissionFile;

class PublicationFileProcessor
{
    /** Process data for the PublicationFile */
    public static function process(object $data, int $submissionId, string $filePath, int $publicationFormatId, int $genreId, int $fileId): void
    {
        $mimeType = PKPString::mime_content_type($filePath);

        $submissionFileDao = CachedDaos::getSubmissionFileDao();

        $submissionFile = $submissionFileDao->newDataObject();
        $submissionFile->setData('submissionId', $submissionId);
        $submissionFile->setData('uploaderUserId', CachedEntities::getCachedUser()->getId());
        $submissionFile->setSubmissionLocale($data->locale);
        $submissionFile->setGenreId($genreId);
        $submissionFile->setFileStage(SubmissionFile::SUBMISSION_FILE_PROOF);
        $submissionFile->setAssocType(Application::ASSOC_TYPE_REPRESENTATION);
        $submissionFile->setData('assocId', $publicationFormatId);
        $submissionFile->setData('createdAt', Core::getCurrentDate());
        $submissionFile->setDateModified(Core::getCurrentDate());
        $submissionFile->setData('mimetype', $mimeType);
        $submissionFile->setData('fileId', $fileId);
        $submissionFile->setData('name', pathinfo($filePath, PATHINFO_FILENAME), $data->locale);

        // Assume open access, no price.
        $submissionFile->setDirectSalesPrice(0);
        $submissionFile->setSalesType('openAccess');

        $submissionFileDao->insert($submissionFile);
    }
}
