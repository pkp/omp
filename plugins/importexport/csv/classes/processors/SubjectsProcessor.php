<?php

/**
 * @file plugins/importexport/csv/classes/processors/SubjectsProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubjectsProcessor
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing subjects.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;

class SubjectsProcessor
{
    /** Process data for Subjects */
    public static function process(object $data, int $publicationId): void
    {
        $subjectsList = [$data->locale => array_map('trim', explode(';', $data->subjects))];

        if (count($subjectsList[$data->locale]) > 0) {
            $submissionSubjectDao = CachedDaos::getSubmissionSubjectDao();
            $submissionSubjectDao->insertSubjects($subjectsList, $publicationId);
        }
    }
}
