<?php
/**
 * @file classes/decision/Repository.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage editorial decisions.
 */

namespace APP\decision;

use APP\decision\types\AcceptFromInternal;
use APP\decision\types\CancelInternalReviewRound;
use APP\decision\types\DeclineInternal;
use APP\decision\types\NewInternalReviewRound;
use APP\decision\types\RecommendAcceptInternal;
use APP\decision\types\RecommendDeclineInternal;
use APP\decision\types\RecommendResubmitInternal;
use APP\decision\types\RecommendRevisionsInternal;
use APP\decision\types\RecommendSendExternalReview;
use APP\decision\types\RequestRevisionsInternal;
use APP\decision\types\RevertDeclineInternal;
use APP\decision\types\SendExternalReview;
use APP\decision\types\SendInternalReview;
use APP\decision\types\SkipInternalReview;
use Illuminate\Database\Eloquent\Collection;
use PKP\decision\types\Accept;
use PKP\decision\types\BackFromCopyediting;
use PKP\decision\types\BackFromProduction;
use PKP\decision\types\CancelReviewRound;
use PKP\decision\types\Decline;
use PKP\decision\types\InitialDecline;
use PKP\decision\types\NewExternalReviewRound;
use PKP\decision\types\RecommendAccept;
use PKP\decision\types\RecommendDecline;
use PKP\decision\types\RecommendResubmit;
use PKP\decision\types\RecommendRevisions;
use PKP\decision\types\RequestRevisions;
use PKP\decision\types\Resubmit;
use PKP\decision\types\RevertDecline;
use PKP\decision\types\RevertInitialDecline;
use PKP\decision\types\SendToProduction;
use PKP\decision\types\SkipExternalReview;
use PKP\notification\Notification;
use PKP\plugins\Hook;

class Repository extends \PKP\decision\Repository
{
    /** The valid decision types */
    protected ?Collection $decisionTypes;

    public function getDecisionTypes(): Collection
    {
        if (!isset($this->decisionTypes)) {
            $decisionTypes = new Collection([
                new Accept(),
                new AcceptFromInternal(),
                new Decline(),
                new DeclineInternal(),
                new InitialDecline(),
                new NewInternalReviewRound(),
                new NewExternalReviewRound(),
                new RecommendAccept(),
                new RecommendDecline(),
                new RecommendResubmit(),
                new RecommendRevisions(),
                new RecommendAcceptInternal(),
                new RecommendDeclineInternal(),
                new RecommendResubmitInternal(),
                new RecommendRevisionsInternal(),
                new RecommendSendExternalReview(),
                new RequestRevisionsInternal(),
                new RequestRevisions(),
                new Resubmit(),
                new RevertDecline(),
                new RevertDeclineInternal(),
                new RevertInitialDecline(),
                new SendExternalReview(),
                new SendInternalReview(),
                new SendToProduction(),
                new SkipInternalReview(),
                new SkipExternalReview(),
                new BackFromProduction(),
                new BackFromCopyediting(),
                new CancelInternalReviewRound(),
                new CancelReviewRound(),
            ]);
            Hook::call('Decision::types', [$decisionTypes]);
            $this->decisionTypes = $decisionTypes;
        }

        return $this->decisionTypes;
    }

    public function getDeclineDecisionTypes(): array
    {
        return [
            new InitialDecline(),
            new DeclineInternal(),
            new Decline(),
        ];
    }

    public function isRecommendation(int $decision): bool
    {
        return in_array($decision, [
            Decision::RECOMMEND_ACCEPT,
            Decision::RECOMMEND_DECLINE,
            Decision::RECOMMEND_PENDING_REVISIONS,
            Decision::RECOMMEND_RESUBMIT,
            Decision::RECOMMEND_ACCEPT_INTERNAL,
            Decision::RECOMMEND_DECLINE_INTERNAL,
            Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL,
            Decision::RECOMMEND_RESUBMIT_INTERNAL,
            Decision::RECOMMEND_EXTERNAL_REVIEW
        ]);
    }

    protected function getReviewNotificationTypes(): array
    {
        return [
            Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS,
            Notification::NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS,
        ];
    }

    public function getDecisionTypesMadeByRecommendingUsers(int $stageId): array
    {
        $recommendatorsAvailableDecisions = [];
        switch($stageId) {
            case WORKFLOW_STAGE_ID_SUBMISSION:
                $recommendatorsAvailableDecisions = [
                    new SendInternalReview()
                ];
        }

        Hook::call('Workflow::RecommendatorDecisions', [&$recommendatorsAvailableDecisions, $stageId]);

        return $recommendatorsAvailableDecisions;
    }
}
