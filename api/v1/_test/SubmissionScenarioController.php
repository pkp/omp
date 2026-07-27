<?php

/**
 * @file api/v1/_test/SubmissionScenarioController.php
 *
 * Copyright (c) 2023-2026 Simon Fraser University
 * Copyright (c) 2023-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionScenarioController
 *
 * @ingroup api_v1__test
 *
 * @brief OMP overlay for the shared submission scenario endpoint.
 *
 * Adds the OMP concepts a seeded monograph needs — the SERIES it appears in and
 * its position there — and, the part that is genuinely OMP-shaped, teaches the
 * shared review-round builder that this application has TWO review stages.
 *
 * ## Internal and External Review
 *
 * OMP is the only app with an Internal Review stage, so `reviewRounds` here
 * carries a `stage` of `internal` or `external` (the design record's
 * "internal/external key on review rounds"). The shared schema declares
 * `reviewRounds` without it and closes the object with
 * `additionalProperties: false`; overlay properties are merged over the core
 * schema, so redeclaring the whole property here is what adds the key —
 * app-side, with no change to `lib/pkp`.
 *
 * The shared builder resolves one review stage per request from
 * `Application::getReviewStages()` and takes its LAST entry. OMP declares that
 * roster as `[EXTERNAL_REVIEW, INTERNAL_REVIEW]` — external first — so the
 * shared default would silently mean "internal review". This subclass answers
 * `reviewStageId()` from the stage the round asked for instead, and never lets
 * the ordering of that roster decide anything.
 *
 * The decision that opens a round follows from where the submission IS, because
 * that is how an editor reaches the stage in the UI:
 *
 *   Submission      → Internal Review   `sendInternalReview`
 *   Submission      → External Review   `skipInternalReview`
 *   Internal Review → External Review   `sendExternalReview`
 */

namespace APP\API\v1\_test;

use APP\facades\Repo;
use APP\submission\Submission;
use PKP\API\v1\_test\PKPSubmissionScenarioController;
use PKP\context\Context;
use PKP\db\DAORegistry;
use PKP\submission\reviewRound\ReviewRoundDAO;
use PKP\testing\scenario\ScenarioException;

class SubmissionScenarioController extends PKPSubmissionScenarioController
{
    public const REVIEW_STAGE_INTERNAL = 'internal';
    public const REVIEW_STAGE_EXTERNAL = 'external';

    /**
     * Which of OMP's two review stages the rounds currently being built belong
     * to. `applyReviewRounds()` sets it; `reviewStageId()` and
     * `promoteToReviewDecision()` — both called by the shared builder without
     * arguments — read it.
     */
    protected string $targetReviewStage = self::REVIEW_STAGE_EXTERNAL;

    /**
     * @copydoc \PKP\API\v1\_test\PKPTestApiController::schemaOverlayProperties()
     */
    public function schemaOverlayProperties(): array
    {
        return [
            'series' => [
                'type' => 'string',
                'description' => 'OMP overlay. Path of the series to publish in. Optional: OMP series are optional and a monograph with none is a valid, common state, so there is no default.',
            ],
            'seriesPosition' => [
                'type' => 'string',
                'description' => 'OMP overlay. Position within the series ("Volume 2"). Requires `series`.',
            ],

            // Redeclares the shared `reviewRounds` property, adding `stage`.
            // SpecValidator::withOverlay() merges overlay properties over the
            // core schema, so this REPLACES the shared definition rather than
            // extending it — the rest of the shape is kept identical on purpose,
            // INCLUDING every per-reviewer key the shared builder understands.
            // A key the shared schema grows must be mirrored here or the overlay
            // silently narrows it away for OMP alone (this bit U26: `method`,
            // the completed/confirmed statuses, the comment keys and the
            // attachment were all live in the shared processor and rejected by
            // this overlay).
            'reviewRounds' => [
                'type' => 'array',
                'description' => 'Reviewers per review round. OMP has two review stages: each entry names its `stage`, and entry i targets round i+1 OF THAT STAGE. Rounds beyond the first must be opened by a decision in `decisions` (newInternalReviewRound / newExternalReviewRound).',
                'items' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'stage' => [
                            'type' => 'string',
                            'enum' => [self::REVIEW_STAGE_INTERNAL, self::REVIEW_STAGE_EXTERNAL],
                            'description' => 'OMP overlay. Defaults to external — the stage every app that has review has, and the one an OJS-shaped scenario means.',
                        ],
                        'reviewers' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['user'],
                                'properties' => [
                                    'user' => ['type' => 'string', 'minLength' => 1, 'description' => 'username of the reviewer. Reviewers must be enrolled in the group that reaches this stage: internalReviewer for Internal Review, externalReviewer for External Review.'],
                                    'status' => [
                                        'type' => 'string',
                                        'enum' => ['invited', 'accepted', 'declined', 'completed', 'confirmed'],
                                        'description' => "Defaults to invited. 'accepted'/'declined' run the reviewer's own confirm action; 'completed' additionally submits the review through the reviewer's own step-3 form; 'confirmed' additionally has the editor confirm it, the way reading the review from the Reviewers panel does.",
                                    ],
                                    'method' => [
                                        'type' => 'string',
                                        'enum' => ['open', 'anonymous', 'doubleAnonymous'],
                                        'description' => "Review method for this assignment; defaults to the press's default review mode. Only an open review is readable by the author.",
                                    ],
                                    'commentsForAuthor' => [
                                        'type' => 'string',
                                        'description' => 'The review comment the reviewer shares with the author. Requires a completed status.',
                                    ],
                                    'commentsForEditor' => [
                                        'type' => 'string',
                                        'description' => 'The review comment the reviewer addresses to the editor alone. Requires a completed status.',
                                    ],
                                    'recommendation' => [
                                        'type' => 'string',
                                        'description' => "Localized title of one of the press's reviewer recommendation options. Presses ship with NONE (spec register OMP4), so on an unconfigured press this key always throws, naming the empty option list.",
                                    ],
                                    'attachment' => [
                                        'type' => 'boolean',
                                        'description' => 'Attach a reviewer file to the completed review, the way the reviewer\'s upload step does.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    //
    // Review stages
    //

    /**
     * Build each stage's rounds against that stage.
     *
     * The shared implementation walks one stage per request. OMP groups the
     * round specs by the stage they name and runs the shared walk once per
     * group, internal first — which is also the order the workflow moves in, so
     * the promoting decision for the external group correctly finds a submission
     * sitting in Internal Review when the spec asked for both.
     *
     * @throws ScenarioException
     */
    protected function applyReviewRounds(array $spec, Context $context): array
    {
        $roundSpecs = array_values($spec['reviewRounds'] ?? []);

        if (empty($roundSpecs)) {
            return [];
        }

        $echo = [];

        foreach ([self::REVIEW_STAGE_INTERNAL, self::REVIEW_STAGE_EXTERNAL] as $stage) {
            $positions = array_keys(array_filter(
                $roundSpecs,
                fn (array $roundSpec) => ($roundSpec['stage'] ?? self::REVIEW_STAGE_EXTERNAL) === $stage
            ));

            if (empty($positions)) {
                continue;
            }

            $this->targetReviewStage = $stage;

            $stageSpec = $spec;
            $stageSpec['reviewRounds'] = array_map(fn (int $position) => $roundSpecs[$position], $positions);

            foreach (parent::applyReviewRounds($stageSpec, $context) as $offset => $round) {
                // Keyed by the spec's own position so the response lines up with
                // the request even though the two stages were built separately.
                $echo[$positions[$offset]] = ['stage' => $stage] + $round;
            }
        }

        ksort($echo);

        return array_values($this->withFinalRoundStatuses($echo));
    }

    /**
     * Re-read every echoed round's status once the whole build is done.
     *
     * The shared builder stamps a round's status as soon as that round's
     * reviewers are in place. On OMP a LATER group can still change an EARLIER
     * one — recording `sendExternalReview` sets the internal round it was taken
     * in to REVIEW_ROUND_STATUS_ACCEPTED — so an internal round seeded alongside
     * an external one would otherwise be echoed with a status the database no
     * longer holds. Read it back rather than reason about it.
     *
     * @param array<int, array> $echo
     *
     * @return array<int, array>
     */
    protected function withFinalRoundStatuses(array $echo): array
    {
        /** @var ReviewRoundDAO $reviewRoundDao */
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');

        foreach ($echo as $index => $round) {
            $echo[$index]['status'] = (int) $reviewRoundDao->getById($round['id'])->getStatus();
        }

        return $echo;
    }

    /**
     * The review stage the current round group belongs to.
     *
     * NOT `end(Application::getReviewStages())` — OMP declares that roster
     * external-first, so the shared heuristic would resolve to Internal Review.
     */
    protected function reviewStageId(): ?int
    {
        return $this->targetReviewStage === self::REVIEW_STAGE_INTERNAL
            ? WORKFLOW_STAGE_ID_INTERNAL_REVIEW
            : WORKFLOW_STAGE_ID_EXTERNAL_REVIEW;
    }

    /**
     * The decision that opens round 1 of the stage being built.
     *
     * Which decision that is depends on where the submission stands, exactly as
     * it does for an editor: Send to Internal Review and Skip Internal Review
     * are offered in the Submission stage, Send to External Review inside an
     * internal review round.
     */
    protected function promoteToReviewDecision(): ?string
    {
        if ($this->targetReviewStage === self::REVIEW_STAGE_INTERNAL) {
            return 'sendInternalReview';
        }

        $stageId = (int) Repo::submission()->get($this->currentSubmissionId())->getData('stageId');

        return $stageId === WORKFLOW_STAGE_ID_INTERNAL_REVIEW
            ? 'sendExternalReview'
            : 'skipInternalReview';
    }

    //
    // Series
    //

    /**
     * @copydoc \PKP\API\v1\_test\PKPSubmissionScenarioController::applyPublicationOverlay()
     *
     * @throws ScenarioException
     */
    protected function applyPublicationOverlay(array $spec, Context $context, array &$publicationProps): void
    {
        if (isset($spec['seriesPosition']) && !isset($spec['series'])) {
            throw new ScenarioException('`seriesPosition` needs a `series` to be a position in.', 'seriesPosition');
        }

        if (!isset($spec['series'])) {
            // A monograph with no series is valid in OMP and common — unlike an
            // OJS article, which always has a section.
            return;
        }

        $publicationProps['seriesId'] = $this->resolveSeriesId($spec['series'], $context);

        if (isset($spec['seriesPosition'])) {
            $publicationProps['seriesPosition'] = $spec['seriesPosition'];
        }
    }

    /**
     * @throws ScenarioException
     */
    protected function resolveSeriesId(string $path, Context $context): int
    {
        $series = Repo::section()->getByPath($path, $context->getId());

        if (!$series) {
            $available = Repo::section()->getCollector()
                ->filterByContextIds([$context->getId()])
                ->getMany()
                ->map(fn ($candidate) => $candidate->getPath())
                ->filter()
                ->join(', ');

            throw new ScenarioException(
                "Press '{$context->getPath()}' has no series with path '{$path}'. Available: "
                    . ($available ?: '(none)') . '.',
                'series'
            );
        }

        return $series->getId();
    }

    /**
     * @copydoc \PKP\API\v1\_test\PKPSubmissionScenarioController::submissionEcho()
     */
    protected function submissionEcho(Submission $submission, array $spec): array
    {
        $publication = $submission->getCurrentPublication();

        return array_filter([
            'seriesId' => $publication?->getData('seriesId'),
            'seriesPosition' => $publication?->getData('seriesPosition'),
            'workType' => $submission->getData('workType'),
        ], fn ($value) => $value !== null);
    }
}
