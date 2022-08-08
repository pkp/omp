<?php

/**
 * @file classes/decision/types/BackToSubmissionFromInternalReview.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class decision
 *
 * @brief A decision to return a submission back from the internal review stage
 *   if has more than one internal review round, remains in the external review stage
 *   if has no internal review round, back to submission stage.
 *
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use APP\submission\Submission;
use PKP\db\DAORegistry;
use PKP\decision\types\CancelReviewRound as CancelReviewRound;
use PKP\decision\types\traits\NotifyAuthors;
use PKP\decision\types\traits\NotifyReviewersOfUnassignment;
use PKP\submission\reviewRound\ReviewRoundDAO;

class CancelInternalReviewRound extends CancelReviewRound
{
    use NotifyAuthors;
    use NotifyReviewersOfUnassignment;
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::CANCEL_REVIEW_ROUND;
    }

    /**
     * Determine the new backout stage id for this decision
     *
     * The determining process follows as :
     *
     * If there is more than one internal review round associated with it
     * new stage need to be internal review stage
     *
     * If there is only one internal review round associated with it
     * new stage need to submission stage
     */
    public function getNewStageId(Submission $submission, ?int $reviewRoundId): ?int
    {
        /** @var ReviewRoundDAO $reviewRoundDao */
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');

        if ($reviewRoundDao->getReviewRoundCountBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW) > 1) {
            return WORKFLOW_STAGE_ID_INTERNAL_REVIEW;
        }

        return WORKFLOW_STAGE_ID_SUBMISSION;
    }
}
