<?php

/**
 * @file plugins/importexport/csv/classes/processors/PublicationProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationProcessor
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publications.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\press\Press;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\core\Core;
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
		$publication->setData('datePublished', Core::getCurrentDate());
		$publication->setData('abstract', $sanitizedAbstract, $locale);
		$publication->setData('title', $data->title, $locale);
		$publication->setData('copyrightNotice', $press->getLocalizedData('copyrightNotice', $locale), $locale);

		if ($data->seriesPath) {
			$publication->setData('seriesId', $pressSeriesId);
		}

		$publicationDao->insert($publication);

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
}

