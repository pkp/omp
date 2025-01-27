<?php

/**
 * @file classes/submission/maps/Schema.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Schema
 *
 * @brief Map submissions to the properties defined in the submission schema
 */

namespace APP\submission\maps;

use APP\core\Application;
use APP\decision\types\AcceptFromInternal;
use APP\decision\types\CancelInternalReviewRound;
use APP\decision\types\DeclineInternal;
use APP\decision\types\NewInternalReviewRound;
use APP\decision\types\RequestRevisionsInternal;
use APP\decision\types\RevertDeclineInternal;
use APP\decision\types\SendExternalReview;
use APP\decision\types\SendInternalReview;
use APP\decision\types\SkipInternalReview;
use APP\facades\Repo;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\submission\Submission;
use Illuminate\Support\Collection;
use PKP\db\DAORegistry;
use PKP\decision\DecisionType;
use PKP\decision\types\Accept;
use PKP\decision\types\BackFromCopyediting;
use PKP\decision\types\BackFromProduction;
use PKP\decision\types\CancelReviewRound;
use PKP\decision\types\Decline;
use PKP\decision\types\InitialDecline;
use PKP\decision\types\NewExternalReviewRound;
use PKP\decision\types\RequestRevisions;
use PKP\decision\types\Resubmit;
use PKP\decision\types\RevertDecline;
use PKP\decision\types\RevertInitialDecline;
use PKP\decision\types\SendToProduction;
use PKP\decision\types\SkipExternalReview;
use PKP\plugins\Hook;
use PKP\security\Role;
use PKP\submission\reviewRound\ReviewRound;
use PKP\submission\reviewRound\ReviewRoundDAO;

class Schema extends \PKP\submission\maps\Schema
{
    /** @copydoc \PKP\submission\maps\Schema::getSubmissionsListProps() */
    protected function getSubmissionsListProps(): array
    {
        $props = parent::getSubmissionsListProps();
        $props[] = 'series';
        $props[] = 'category';
        $props[] = 'featured';
        $props[] = 'newRelease';

        return $props;
    }

    /** @copydoc \PKP\submission\maps\Schema::mapByProperties() */
    protected function mapByProperties(array $props, Submission $submission, bool|Collection $anonymizeReviews = false): array
    {
        $output = parent::mapByProperties(array_diff($props, ['recommendationsIn', 'reviewersNotAssigned', 'reviewRounds', 'revisionsRequested', 'revisionsSubmitted']), $submission, $anonymizeReviews);

        $locales = $this->context->getSupportedSubmissionMetaDataLocales();

        if (!in_array($submissionLocale = $submission->getData('locale'), $locales)) {
            $locales[] = $submissionLocale;
        }

        $reviewRounds = $this->getGroupedReviewRoundsFromSubmission($submission);
        $currentReviewRound = $reviewRounds->flatten()->sort()->last(); /** @var ReviewRound|null $currentReviewRound */

        foreach ($props as $prop) {
            switch ($prop) {
                case 'recommendationsIn':
                    $output[$prop] = $currentReviewRound ? $this->areRecommendationsIn($currentReviewRound, $this->stageAssignments) : null;
                    break;
                case 'reviewersNotAssigned':
                    $output[$prop] = $currentReviewRound && $this->reviewAssignments->count() >= intval($this->context->getData('numReviewersPerSubmission'));
                    break;
                case 'reviewRounds':
                    $output[$prop] = $this->getPropertyReviewRounds($reviewRounds->flatten());
                    break;
                case 'revisionsRequested':
                    $output[$prop] = $currentReviewRound && $currentReviewRound->getData('status') == ReviewRound::REVIEW_ROUND_STATUS_REVISIONS_REQUESTED;
                    break;
                case 'revisionsSubmitted':
                    $output[$prop] = $currentReviewRound && $currentReviewRound->getData('status') == ReviewRound::REVIEW_ROUND_STATUS_REVISIONS_SUBMITTED;
                    break;
                case 'featured':
                    $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
                    $output['featured'] = $featureDao->getFeaturedAll($submission->getId());
                    break;
                case 'newRelease':
                    $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
                    $output['newRelease'] = $newReleaseDao->getNewReleaseAll($submission->getId());
                    break;
                case 'urlPublished':
                    $output['urlPublished'] = $this->request->getDispatcher()->url(
                        $this->request,
                        Application::ROUTE_PAGE,
                        $this->context->getPath(),
                        'catalog',
                        'book',
                        [$submission->getBestId()]
                    );
                    break;
            }
        }


        $output = $this->schemaService->addMissingMultilingualValues($this->schemaService::SCHEMA_SUBMISSION, $output, $locales);

        ksort($output);

        return $this->withExtensions($output, $submission);
    }

    /**
     * @return Collection<Collection<ReviewRound>> grouped list of review rounds related to particular submission
     */
    protected function getGroupedReviewRoundsFromSubmission(Submission $submission): Collection
    {
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /** @var ReviewRoundDAO $reviewRoundDao */
        return collect($reviewRoundDao->getBySubmissionId($submission->getId())->toIterator())
            ->groupBy(fn (ReviewRound $reviewRound) => $reviewRound->getData('round'));
    }

    /**
     * Gets the Editorial decisions available to editors for a given stage of a submission
     *
     * This method returns decisions only for active stages. For inactive stages, it returns an empty array.
     *
     * @return DecisionType[]
     *
     * @hook Workflow::Decisions [[&$decisionTypes, $stageId]]
     */
    protected function getAvailableEditorialDecisions(int $stageId, Submission $submission): array
    {
        $request = Application::get()->getRequest();
        $user = $request->getUser();
        $isActiveStage = $submission->getData('stageId') == $stageId;
        $permissions = $this->checkDecisionPermissions($stageId, $submission, $user, $request->getContext()->getId());
        $userHasAccessibleRoles = $user->hasRole([Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_ASSISTANT], $request->getContext()->getId());

        if (!$userHasAccessibleRoles || !$isActiveStage || !$permissions['canMakeDecision']) {
            return [];
        }

        $decisionTypes = []; /** @var DecisionType[] $decisionTypes */

        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /** @var ReviewRoundDAO $reviewRoundDao */
        $reviewRound = $reviewRoundDao->getLastReviewRoundBySubmissionId($submission->getId(), $stageId);

        $isOnlyRecommending = $permissions['isOnlyRecommending'];

        if ($isOnlyRecommending && $stageId === WORKFLOW_STAGE_ID_SUBMISSION) {
            $decisionTypes = Repo::decision()->getDecisionTypesMadeByRecommendingUsers($stageId);
        } else {
            switch ($stageId) {
                case WORKFLOW_STAGE_ID_SUBMISSION:
                    $decisionTypes = [
                        new SkipInternalReview(),
                        new SkipExternalReview(),
                    ];

                    if ($submission->getData('status') === Submission::STATUS_DECLINED) {
                        // when the submission is declined, allow only reverting declined status
                        $decisionTypes = [new RevertInitialDecline()];
                    } elseif ($submission->getData('status') === Submission::STATUS_QUEUED) {
                        $decisionTypes[] = new InitialDecline();
                        $decisionTypes[] = new SendInternalReview();

                    }
                    break;
                case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
                    $decisionTypes = [
                        new RequestRevisionsInternal(),
                        new SendExternalReview(),
                        new AcceptFromInternal(),
                        new NewInternalReviewRound()

                    ];
                    $cancelInternalReviewRound = new CancelInternalReviewRound();

                    if ($cancelInternalReviewRound->canRetract($submission, $reviewRound->getId())) {
                        $decisionTypes[] = $cancelInternalReviewRound;
                    }

                    if ($submission->getData('status') === Submission::STATUS_DECLINED) {
                        // when the submission is declined, allow only reverting declined status
                        $decisionTypes = [new RevertDeclineInternal()];
                    } elseif ($submission->getData('status') === Submission::STATUS_QUEUED) {
                        $decisionTypes[] = new DeclineInternal();
                    }
                    break;
                case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
                    $decisionTypes = [
                        new RequestRevisions(),
                        new Resubmit(),
                        new Accept(),
                        new NewExternalReviewRound()

                    ];

                    $cancelReviewRound = new CancelReviewRound();
                    if ($cancelReviewRound->canRetract($submission, $reviewRound->getId())) {
                        $decisionTypes[] = $cancelReviewRound;
                    }
                    if ($submission->getData('status') === Submission::STATUS_DECLINED) {
                        // when the submission is declined, allow only reverting declined status
                        $decisionTypes = [new RevertDecline()];
                    } elseif ($submission->getData('status') === Submission::STATUS_QUEUED) {
                        $decisionTypes[] = new Decline();
                    }
                    break;
                case WORKFLOW_STAGE_ID_EDITING:
                    $decisionTypes = [
                        new SendToProduction(),
                        new BackFromCopyediting(),
                    ];
                    break;
                case WORKFLOW_STAGE_ID_PRODUCTION:
                    $decisionTypes[] = new BackFromProduction();
                    break;
            }
        }

        Hook::call('Workflow::Decisions', [&$decisionTypes, $stageId]);

        return $decisionTypes;
    }
}
