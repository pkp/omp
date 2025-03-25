<?php

/**
 * @file plugins/importexport/csv/classes/processors/SubjectsProcessor.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubjectsProcessor
 *
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing subjects.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\core\Application;
use APP\facades\Repo;
use PKP\controlledVocab\ControlledVocab;

class SubjectsProcessor
{
    /** Process data for Subjects */
    public static function process(object $data, int $publicationId): void
    {
        $subjectsList = array_map('trim', explode(';', $data->subjects));

        if (count($subjectsList) > 0) {
            Repo::controlledVocab()->insertBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_SUBJECT,
                [$data->locale => $subjectsList],
                Application::ASSOC_TYPE_PUBLICATION,
                $publicationId
            );
        }
    }
}
