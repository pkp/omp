<?php
/**
 * @file classes/decision/types/SendExternalReview.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class decision
 *
 * @brief A decision to send a submission to the external review round
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use APP\facades\Repo;
use APP\submission\Submission;
use PKP\decision\steps\PromoteFiles;
use PKP\decision\types\SendExternalReview as PKPSendExternalReview;
use PKP\submission\reviewRound\ReviewRound;
use PKP\submissionFile\SubmissionFile;

class SendExternalReview extends PKPSendExternalReview
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::EXTERNAL_REVIEW;
    }

    public function getNewStageId(): int
    {
        return WORKFLOW_STAGE_ID_EXTERNAL_REVIEW;
    }

    public function getNewReviewRoundStatus(): ?int
    {
        return ReviewRound::REVIEW_ROUND_STATUS_ACCEPTED;
    }

    /**
     * Get the file promotion step with file promotion lists
     * added to it
     */
    protected function withFilePromotionLists(Submission $submission, PromoteFiles $step): PromoteFiles
    {
        return $step->addFileList(
            __('editor.submission.revisions'),
            Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$submission->getId()])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION])
        );
    }
}
