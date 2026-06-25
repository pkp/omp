<?php

/**
 * @file plugins/importexport/csv/classes/processors/PublicationProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationProcessor
 *
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publications.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\press\Press;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\core\PKPString;

class PublicationProcessor
{
    /** Process initial data for Publication */
    public static function process(Submission $submission, object $data, Press $press, ?int $pressSeriesId = null): Publication
    {
        $publicationDao = CachedDaos::getPublicationDao();
        $sanitizedAbstract = PKPString::stripUnsafeHtml($data->abstract);
        $locale = $data->locale;

        $publication = $publicationDao->newDataObject();
        $publication->setData('submissionId', $submission->getId());
        $publication->setData('version', 1);
        $publication->setData('status', Submission::STATUS_PUBLISHED);
        $publication->setData('datePublished', $data->datePublished);

        $publication->setData('abstract', $sanitizedAbstract, $locale);
        $publication->setData('title', $data->title, $locale);

        if ($data->seriesPath) {
            $publication->setData('seriesId', $pressSeriesId);
        }

        $publicationDao->insert($publication);

        self::setCopyrightFromSystem($submission, $publication, $data->locale);
        $publicationDao->update($publication);

        // Add this publication as the current one, now that we have its ID
        $submission->setData('currentPublicationId', $publication->getId());

        $submissionDao = CachedDaos::getSubmissionDao();
        $submissionDao->update($submission);

        return $publication;
    }

    public static function updatePrimaryContact(Publication $publication, int $authorId): void
    {
        $publication->setData('primaryContactId', $authorId);
        CachedDaos::getPublicationDao()->update($publication);
    }

    public static function updateBookCoverImage(Publication $publication, string $uploadName, object $data): void
    {
        $coverImage = [];
        $coverImage['uploadName'] = $uploadName;
        $coverImage['altText'] = $data->bookCoverImageAltText ?? '';
        $publication->setData('coverImage', [$data->locale => $coverImage]);
        CachedDaos::getPublicationDao()->update($publication);
    }

    private static function setCopyrightFromSystem(Submission $submission, Publication $publication, string $locale): void
    {
        $copyrightHolder = $submission->_getContextLicenseFieldValue(
            null,
            Submission::PERMISSIONS_FIELD_COPYRIGHT_HOLDER,
            $publication
        );
        $publication->setData('copyrightHolder', $copyrightHolder);

        $copyrightYear = $submission->_getContextLicenseFieldValue(
            null,
            Submission::PERMISSIONS_FIELD_COPYRIGHT_YEAR,
            $publication
        );
        $publication->setData('copyrightYear', $copyrightYear);

        $licenseUrl = $submission->_getContextLicenseFieldValue(
            null,
            Submission::PERMISSIONS_FIELD_LICENSE_URL,
            $publication
        );
        $publication->setData('licenseUrl', $licenseUrl);
    }
}
