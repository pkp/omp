<?php
/**
 * @file classes/decision/types/traits/InInternalReviewRound.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class decision
 *
 * @brief Helper functions for decisions taken in an internal review round
 */

namespace APP\decision\types\traits;

use PKP\decision\types\traits\InExternalReviewRound;
use PKP\submissionFile\SubmissionFile;

trait InInternalReviewRound
{
    use InExternalReviewRound;

    /** @copydoc DecisionType::getStageId() */
    public function getStageId(): int
    {
        return WORKFLOW_STAGE_ID_INTERNAL_REVIEW;
    }

    /** Helper method so self::getFileAttachers() can be extended for other review stages */
    protected function getRevisionFileStage(): int
    {
        return SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION;
    }

    /** Helper method so self::getFileAttachers() can be extended for other review stages */
    protected function getReviewFileStage(): int
    {
        return SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE;
    }

    /**
     * Get the submission file stages that are permitted to be attached to emails
     * sent in this decision
     *
     * @return array<int>
     */
    protected function getAllowedAttachmentFileStages(): array
    {
        return [
            SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT,
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
        ];
    }
}
