<?php

/**
 * @file plugins/importexport/csv/classes/processors/AuthorsProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorsProcessor
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing authors.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\publication\Publication;

class AuthorsProcessor
{
    /** Process data for Submission authors */
    public static function process(object $data, string $contactEmail, int $submissionId, Publication $publication, int $userGroupId): void
    {
        $authorDao = CachedDaos::getAuthorDao();
        $authorsString = array_map('trim', explode(';', $data->authorString));

        foreach ($authorsString as $index => $authorString) {
            // Examine the author string. Best case is: "Given1,Family1,email@address.com;Given2,Family2,email@address.com", etc
            // But default to press email address based on press path if not present.
            $givenName = $familyName = $emailAddress = null;
            [$givenName, $familyName, $emailAddress] = array_map('trim', explode(',', $authorString));

            if (empty($emailAddress)) {
                $emailAddress = $contactEmail;
            }

            $emailAddress = trim($emailAddress);
            $author = $authorDao->newDataObject();
            $author->setSubmissionId($submissionId);
            $author->setUserGroupId($userGroupId);
            $author->setGivenName($givenName, $data->locale);
            $author->setFamilyName($familyName, $data->locale);
            $author->setEmail($emailAddress);
            $author->setData('publicationId', $publication->getId());
            $authorDao->insert($author);

            if (!$index) {
                $author->setPrimaryContact(true);
                $authorDao->update($author);

                PublicationProcessor::updatePrimaryContact($publication, $author->getId());
            }
        }
    }
}
