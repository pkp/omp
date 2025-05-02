<?php

/**
 * @file plugins/importexport/csv/classes/processors/PublicationDateProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateProcessor
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publication dates.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;

class PublicationDateProcessor
{
    /** Process data for the PublicationDate */
    public static function process(int $year, int $publicationFormatId): void
    {
        $publicationDateDao = CachedDaos::getPublicationDateDao();

        $publicationDate = $publicationDateDao->newDataObject();
        $publicationDate->setDateFormat('05'); // List55, YYYY
        $publicationDate->setRole('01'); // List163, Publication Date
        $publicationDate->setDate($year);
        $publicationDate->setPublicationFormatId($publicationFormatId);
        $publicationDateDao->insertObject($publicationDate);
    }
}
