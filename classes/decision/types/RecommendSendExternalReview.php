<?php
/**
 * @file classes/decision/types/RecommendSendExternalReview.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RecommendSendExternalReview
 *
 * @brief A recommendation to accept a submission for publication.
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use APP\submission\Submission;
use PKP\decision\DecisionType;
use PKP\decision\types\traits\IsRecommendation;

class RecommendSendExternalReview extends DecisionType
{
    use InInternalReviewRound;
    use IsRecommendation;

    public function getDecision(): int
    {
        return Decision::RECOMMEND_EXTERNAL_REVIEW;
    }

    public function getNewStageId(Submission $submission, ?int $reviewRoundId): ?int
    {
        return null;
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
        return __('editor.submission.recommend.sendExternalReview', [], $locale);
    }

    public function getDescription(?string $locale = null): string
    {
        return __('editor.submission.recommend.sendExternalReview.description', [], $locale);
    }

    public function getLog(): string
    {
        return 'editor.submission.recommend.sendExternalReview.log';
    }

    public function getCompletedLabel(): string
    {
        return __('editor.submission.recommend.completed');
    }

    public function getCompletedMessage(Submission $submission): string
    {
        return __('editor.submission.recommend.completed.description');
    }

    public function getRecommendationLabel(): string
    {
        return __('editor.submission.decision.sendExternalReview');
    }
}
