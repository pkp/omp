<?php

/**
 * @file plugins/importexport/csv/classes/processors/PublicationFormatProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatProcessor
 *
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publication formats.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;

class PublicationFormatProcessor
{
    /** Process data for the PublicationFormat */
    public static function process(int $submissionId, int $publicationId, string $extension, object $data): int
    {
        $publicationFormatDao = CachedDaos::getPublicationFormatDao();

        $publicationFormat = $publicationFormatDao->newDataObject();
        $publicationFormat->setData('submissionId', $submissionId);
        $publicationFormat->setData('publicationId', $publicationId);
        $publicationFormat->setPhysicalFormat(false);
        $publicationFormat->setIsApproved(true);
        $publicationFormat->setIsAvailable(true);
        $publicationFormat->setProductAvailabilityCode('20'); // ONIX code for Available.
        $publicationFormat->setEntryKey('DA'); // ONIX code for Digital
        $publicationFormat->setData('name', mb_strtoupper($extension), $data->locale);
        $publicationFormat->setSequence(REALLY_BIG_NUMBER);

        $publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

        if ($data->doi) {
            $publicationFormatDao->changePubId($publicationFormatId, 'doi', $data->doi);
        }

        return $publicationFormat->getId();
    }
}
