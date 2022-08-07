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
use APP\submission\Submission;
use Illuminate\Validation\Validator;
use PKP\context\Context;
use PKP\db\DAORegistry;
use PKP\decision\DecisionType;
use PKP\decision\Steps;
use PKP\decision\steps\Email;
use PKP\decision\types\interfaces\DecisionRetractable;
use PKP\decision\types\traits\NotifyAuthors;
use PKP\decision\types\traits\NotifyReviewersOfUnassignment;
use PKP\mail\mailables\ReviewerUnassign;
use PKP\security\Role;
use PKP\submission\reviewAssignment\ReviewAssignmentDAO;
use PKP\submission\reviewRound\ReviewRound;
use PKP\user\User;
use PKP\submission\reviewRound\ReviewRoundDAO;
use APP\decision\types\traits\InInternalReviewRound;
use APP\mail\mailables\DecisionBackFromInternalReviewNotifyAuthor;
use PKP\decision\types\traits\CanRetractReviewRound;

class BackFromInternalReview extends DecisionType implements DecisionRetractable
{
    use NotifyAuthors;
    use NotifyReviewersOfUnassignment;
    use InInternalReviewRound;
    use CanRetractReviewRound;

    protected ?int $backoutStageId = null;

    public function getDecision(): int
    {
        return Decision::BACK_FROM_INTERNAL_REVIEW;
    }

    public function getNewStageId(): ?int
    {
        return $this->backoutStageId;
    }

    public function getNewStatus(): ?int
    {
        return null;
    }

    public function getNewReviewRoundStatus(): ?int
    {
        return null;
    }

    public function getLabel(?string $locale = null): string
    {
        return __('editor.submission.decision.backFromInternalReview', [], $locale);
    }

    public function getDescription(?string $locale = null): string
    {
        return __('editor.submission.decision.backFromInternalReview.description', [], $locale);
    }

    public function getLog(): string
    {
        return 'editor.submission.decision.backFromInternalReview.log';
    }

    public function getCompletedLabel(): string
    {
        return __('editor.submission.decision.backFromInternalReview.completed');
    }

    public function getCompletedMessage(Submission $submission): string
    {
        return __('editor.submission.decision.backFromInternalReview.completed.description', ['title' => $submission->getLocalizedFullTitle()]);
    }

    public function validate(array $props, Submission $submission, Context $context, Validator $validator, ?int $reviewRoundId = null)
    {
        // If there is no review round id, a validation error will already have been set
        if (!$reviewRoundId) {
            return;
        }

        parent::validate($props, $submission, $context, $validator, $reviewRoundId);

        if (!$this->canRetract($submission, $reviewRoundId)) {
            $validator->errors()->add('restriction', __('editor.submission.decision.backFromInternalReview.restriction'));
        }

        if (!isset($props['actions'])) {
            return;
        }

        foreach ((array) $props['actions'] as $index => $action) {
            $actionErrorKey = 'actions.' . $index;
            switch ($action['id']) {
                case $this->ACTION_NOTIFY_AUTHORS:
                    $this->validateNotifyAuthorsAction($action, $actionErrorKey, $validator, $submission);
                    break;
                case $this->ACTION_NOTIFY_REVIEWERS:
                    $this->validateNotifyReviewersAction($action, $actionErrorKey, $validator, $submission, $reviewRoundId, self::REVIEW_ASSIGNMENT_ACTIVE);
                    break;
            }
        }
    }

    public function runAdditionalActions(Decision $decision, Submission $submission, User $editor, Context $context, array $actions)
    {
        parent::runAdditionalActions($decision, $submission, $editor, $context, $actions);

        foreach ($actions as $action) {
            switch ($action['id']) {
                case $this->ACTION_NOTIFY_AUTHORS:
                    $this->sendAuthorEmail(
                        new DecisionBackFromInternalReviewNotifyAuthor($context, $submission, $decision),
                        $this->getEmailDataFromAction($action),
                        $editor,
                        $submission,
                        $context
                    );
                    break;
                case $this->ACTION_NOTIFY_REVIEWERS:
                    $this->sendReviewersEmail(
                        new ReviewerUnassign($context, $submission, null, $decision),
                        $this->getEmailDataFromAction($action),
                        $editor,
                        $submission
                    );
                    break;
            }
        }

        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /** @var ReviewRoundDAO $reviewRoundDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO'); /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewRoundId = $decision->getData('reviewRoundId');

        $reviewAssignmentDao->deleteByReviewRoundId($reviewRoundId);
        $reviewRoundDao->deleteById($reviewRoundId);
    }

    public function getSteps(Submission $submission, Context $context, User $editor, ?ReviewRound $reviewRound): ?Steps
    {
        $steps = new Steps($this, $submission, $context, $reviewRound);

        $fakeDecision = $this->getFakeDecision($submission, $editor);
        $fileAttachers = $this->getFileAttachers($submission, $context);

        $authors = $steps->getStageParticipants(Role::ROLE_ID_AUTHOR);
        if (count($authors)) {
            $mailable = new DecisionBackFromInternalReviewNotifyAuthor($context, $submission, $fakeDecision);
            $steps->addStep(new Email(
                $this->ACTION_NOTIFY_AUTHORS,
                __('editor.submission.decision.notifyAuthors'),
                __('editor.submission.decision.backFromInternalReview.notifyAuthorsDescription'),
                $authors,
                $mailable
                    ->sender($editor)
                    ->recipients($authors),
                $context->getSupportedFormLocales(),
                $fileAttachers
            ));
        }

        $reviewAssignments = $this->getReviewAssignments($submission->getId(), $reviewRound->getId(), self::REVIEW_ASSIGNMENT_ACTIVE);
        if (count($reviewAssignments)) {
            $reviewers = $steps->getReviewersFromAssignments($reviewAssignments);
            $mailable = new ReviewerUnassign($context, $submission, null, $fakeDecision);
            $steps->addStep((new Email(
                $this->ACTION_NOTIFY_REVIEWERS,
                __('editor.submission.decision.notifyReviewers'),
                __('editor.submission.decision.reviewerUnassigned.notifyReviewers.description'),
                $reviewers,
                $mailable->sender($editor),
                $context->getSupportedFormLocales(),
                $fileAttachers
            ))->canChangeRecipients(true));
        }

        return $steps;
    }

    /**
     * Determine if can back out form current external review round to previous external review round
     * 
     * The determining process follows as :
     *      If there is any submitted review by reviewer that is not cancelled, can not back out
     *      If there is any completed review by reviewer, can not back out
     */
    public function canRetract(Submission $submission, ?int $reviewRoundId): bool
    {
        if (!$reviewRoundId) {
            return false;
        }

        $confirmedReviewerIds = $this->getReviewerIds($submission->getId(), $reviewRoundId, self::REVIEW_ASSIGNMENT_CONFIRMED);
        if (count($confirmedReviewerIds) > 0) {
            return false;
        }

        $completedReviewAssignments = $this->getReviewAssignments($submission->getId(), $reviewRoundId, self::REVIEW_ASSIGNMENT_COMPLETED);
        if (count($completedReviewAssignments) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Determine the possible backout stage id for this decision
     * 
     * The determining process follows as :
     * 
     * If there is more than one internal review round associated with it
     * backoutable stage need to be internal review stage
     * 
     * If there is only one internal review round associated with it
     * backoutable stage need to submission stage
     */
    public function deduceBackoutableStageId(Submission $submission, ?int $reviewRoundId): ?int 
    {
        if ( $this->backoutStageId ) {
            return $this->backoutStageId;
        }
        
        /** @var ReviewRoundDAO $reviewRoundDao */
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');

        if ( $reviewRoundDao->getReviewRoundCountBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW) > 1 ) {
            $this->backoutStageId = WORKFLOW_STAGE_ID_INTERNAL_REVIEW;
            return $this->backoutStageId;
        }

        $this->backoutStageId = WORKFLOW_STAGE_ID_SUBMISSION;
        return $this->backoutStageId;
    }
}
