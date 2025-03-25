<?php

/**
 * @file plugins/importexport/csv/classes/processors/ProcessSubmission.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ProcessSubmission
 *
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing submissions.
 */

namespace APP\plugins\importexport\csv\classes\processors;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\submission\Submission;

class SubmissionProcessor
{
    /**
     * Process initial data for Submission
     */
    public static function process(object $data, int $pressId): Submission
    {
        $submissionDao = CachedDaos::getSubmissionDao();

        $submission = $submissionDao->newDataObject();
        $submission->setData('contextId', $pressId);
        $submission->stampLastActivity();
        $submission->stampModified();
        $submission->setData('status', Submission::STATUS_PUBLISHED);
        $submission->setData('workType', $data->isEditedVolume == 1 ? WORK_TYPE_EDITED_VOLUME : WORK_TYPE_AUTHORED_WORK);
        $submission->setData('locale', $data->locale);
        $submission->setData('stageId', WORKFLOW_STAGE_ID_PRODUCTION);
        $submission->setData('submissionProgress', 0);
        $submission->setData('abstract', $data->abstract, $data->locale);
        $submissionDao->insert($submission);

        return $submission;
    }
}
