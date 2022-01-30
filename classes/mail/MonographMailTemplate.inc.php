<?php

/**
 * @file classes/mail/MonographMailTemplate.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographMailTemplate
 * @ingroup mail
 *
 * @brief Subclass of MailTemplate for sending emails related to submissions.
 *
 * This allows for submission-specific functionality like logging, etc.
 */

namespace APP\mail;

use PKP\db\DAORegistry;
use PKP\mail\SubmissionMailTemplate;

class MonographMailTemplate extends SubmissionMailTemplate
{
    /**
     * Assign parameters to the mail template.
     *
     * @param array $paramArray
     */
    public function assignParams($paramArray = [])
    {
        $submission = $this->submission;
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $series = $seriesDao->getById($submission->getSeriesId());
        $paramArray['seriesPath'] = $series ? $series->getPath() : '';
        $paramArray['seriesName'] = $series ? $series->getLocalizedTitle() : '';
        parent::assignParams($paramArray);
    }

    /**
     *  Send this email to all assigned series editors in the given stage
     *
     * @param int $submissionId
     * @param int $stageId
     */
    public function toAssignedSeriesEditors($submissionId, $stageId)
    {
        return $this->toAssignedSubEditors($submissionId, $stageId);
    }

    /**
     * CC this email to all assigned series editors in the given stage
     *
     * @param int $submissionId
     * @param int $stageId
     *
     * @return array of Users (note, this differs from OxS which returns EditAssignment objects)
     */
    public function ccAssignedSeriesEditors($submissionId, $stageId)
    {
        return $this->ccAssignedSubEditors($submissionId, $stageId);
    }

    /**
     * BCC this email to all assigned series editors in the given stage
     *
     * @param int $submissionId
     * @param int $stageId
     */
    public function bccAssignedSeriesEditors($submissionId, $stageId)
    {
        return $this->bccAssignedSubEditors($submissionId, $stageId);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\mail\MonographMailTemplate', '\MonographMailTemplate');
}
