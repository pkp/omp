<?php

/**
 * @file classes/decision/types/WithdrawInInternalReview.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WithdrawInInternalReview
 *
 * @brief A decision to withdraw a submission in the internal review stage.
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use APP\submission\Submission;
use Illuminate\Validation\Validator;
use PKP\context\Context;
use PKP\decision\Steps;
use PKP\decision\steps\Email;
use PKP\decision\types\Withdraw;
use PKP\mail\mailables\DecisionWithdrawNotifyAuthor;
use PKP\security\Role;
use PKP\submission\reviewRound\ReviewRound;
use PKP\user\User;

class WithdrawInInternalReview extends Withdraw
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::WITHDRAW_INTERNAL_REVIEW;
    }

    public function validate(array $props, Submission $submission, Context $context, Validator $validator, ?int $reviewRoundId = null)
    {
        if (!$reviewRoundId) {
            return;
        }

        parent::validate($props, $submission, $context, $validator, $reviewRoundId);
    }

    public function getSteps(Submission $submission, Context $context, User $editor, ?ReviewRound $reviewRound): Steps
    {
        $steps = new Steps($this, $submission, $context, $reviewRound);

        $fakeDecision = $this->getFakeDecision($submission, $editor, $reviewRound);
        $fileAttachers = $this->getFileAttachers($submission, $context, $reviewRound);

        $authors = $steps->getStageParticipants(Role::ROLE_ID_AUTHOR);
        if (count($authors)) {
            $mailable = new DecisionWithdrawNotifyAuthor($context, $submission, $fakeDecision);
            $steps->addStep(new Email(
                $this->ACTION_NOTIFY_AUTHORS,
                __('editor.submission.decision.notifyAuthors'),
                __('editor.submission.decision.withdraw.notifyAuthorsDescription'),
                $authors,
                $mailable
                    ->sender($editor)
                    ->recipients($authors),
                $context->getSupportedFormLocales(),
                $fileAttachers
            ));
        }

        return $steps;
    }
}
