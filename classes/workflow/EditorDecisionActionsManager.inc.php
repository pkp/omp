<?php

/**
 * @file classes/workflow/EditorDecisionActionsManager.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionActionsManager
 * @ingroup classes_workflow
 *
 * @brief Wrapper class for create and assign editor decisions actions to template manager.
 */

namespace APP\workflow;

use PKP\workflow\PKPEditorDecisionActionsManager;
use PKP\submission\PKPSubmission;
use PKP\db\DAORegistry;

class EditorDecisionActionsManager extends PKPEditorDecisionActionsManager
{
    // Submission stage decision actions.
    public const SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW = 1;

    // Submission and review stages decision actions.
    public const SUBMISSION_EDITOR_DECISION_ACCEPT = 2;
    public const SUBMISSION_EDITOR_DECISION_DECLINE = 6;

    // Review stage decisions actions.
    public const SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW = 3;
    public const SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS = 4;
    public const SUBMISSION_EDITOR_DECISION_RESUBMIT = 5;
    public const SUBMISSION_EDITOR_DECISION_NEW_ROUND = 16;

    // Review stage recommendation actions.
    public const SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW = 15;

    // Editorial stage decision actions.
    public const SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION = 7;

    /**
     * Get decision actions labels.
     *
     * @param $context Context
     * @param $stageId int
     * @param $decisions array
     *
     * @return array
     */
    public function getActionLabels($context, $submission, $stageId, $decisions)
    {
        $allDecisionsData =
            $this->_submissionStageDecisions($submission, $stageId) +
            $this->_internalReviewStageDecisions($context, $submission) +
            $this->_externalReviewStageDecisions($context, $submission) +
            $this->_editorialStageDecisions();

        $actionLabels = [];
        foreach ($decisions as $decision) {
            if ($allDecisionsData[$decision]['title']) {
                $actionLabels[$decision] = $allDecisionsData[$decision]['title'];
            } else {
                assert(false);
            }
        }

        return $actionLabels;
    }

    /**
     * Check for editor decisions in the review round.
     *
     * @param $context Context
     * @param $reviewRound ReviewRound
     * @param $decisions array
     *
     * @return boolean
     */
    public function getEditorTakenActionInReviewRound($context, $reviewRound, $decisions = [])
    {
        $editDecisionDao = DAORegistry::getDAO('EditDecisionDAO'); /* @var $editDecisionDao EditDecisionDAO */
        $editorDecisions = $editDecisionDao->getEditorDecisions($reviewRound->getSubmissionId(), $reviewRound->getStageId(), $reviewRound->getRound());

        if (empty($decisions)) {
            $submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
            $submission = $submissionDao->getById($reviewRound->getSubmissionId());
            $decisions = array_keys($this->_internalReviewStageDecisions($context, $submission));
        }
        $takenDecision = false;
        foreach ($editorDecisions as $decision) {
            if (in_array($decision['decision'], $decisions)) {
                $takenDecision = true;
                break;
            }
        }

        return $takenDecision;
    }

    /**
     * @copydoc PKPEditorDecisionActionsManager::getStageDecisions()
     */
    public function getStageDecisions($context, $submission, $stageId, $makeDecision = true)
    {
        switch ($stageId) {
            case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
                return $this->_internalReviewStageDecisions($context, $submission, $stageId, $makeDecision);
        }
        return parent::getStageDecisions($context, $submission, $stageId, $makeDecision);
    }

    /**
     * Get an associative array matching editor recommendation codes with locale strings.
     * (Includes default '' => "Choose One" string.)
     *
     * @param $stageId integer
     *
     * @return array recommendation => localeString
     */
    public function getRecommendationOptions($stageId)
    {
        $recommendationOptions = parent::getRecommendationOptions($stageId);
        if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
            $recommendationOptions[self::SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW] = 'editor.submission.decision.sendExternalReview';
        }
        return $recommendationOptions;
    }

    //
    // Private helper methods.
    //
    /**
     * @copydoc PKPEditorDecisionActionsManager::_submissionStageDecisions()
     */
    protected function _submissionStageDecisions($submission, $stageId, $makeDecision = true)
    {
        $decisions = parent::_submissionStageDecisions($submission, $stageId, $makeDecision);
        $decisions[self::SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW] = [
            'name' => 'internalReview',
            'operation' => 'internalReview',
            'title' => 'editor.submission.decision.sendInternalReview',
        ];
        return $decisions;
    }

    /**
     * Define and return editor decisions for the review stage.
     * If the user cannot make decisions i.e. if it is a recommendOnly user,
     * there will be no decisions options in the review stage.
     *
     * @param $makeDecision boolean If the user can make decisions
     *
     * @return array
     */
    protected function _internalReviewStageDecisions($context, $submission, $makeDecision = true)
    {
        $decisions = [];
        if ($makeDecision) {
            $decisions = [
                self::SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => [
                    'operation' => 'sendReviewsInReview',
                    'name' => 'requestRevisions',
                    'title' => 'editor.submission.decision.requestRevisions',
                ],
                self::SUBMISSION_EDITOR_DECISION_RESUBMIT => [
                    'name' => 'resubmit',
                    'title' => 'editor.submission.decision.resubmit',
                ],
                self::SUBMISSION_EDITOR_DECISION_NEW_ROUND => [
                    'name' => 'newround',
                    'title' => 'editor.submission.decision.newRound',
                ],
                self::SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => [
                    'operation' => 'promoteInReview',
                    'name' => 'externalReview',
                    'title' => 'editor.submission.decision.sendExternalReview',
                    'toStage' => 'workflow.review.externalReview',
                ],
                self::SUBMISSION_EDITOR_DECISION_ACCEPT => [
                    'operation' => 'promoteInReview',
                    'name' => 'accept',
                    'title' => 'editor.submission.decision.accept',
                    'toStage' => 'submission.copyediting',
                ],
            ];

            if ($submission->getStatus() == PKPSubmission::STATUS_QUEUED) {
                $decisions = $decisions + [
                    self::SUBMISSION_EDITOR_DECISION_DECLINE => [
                        'operation' => 'sendReviewsInReview',
                        'name' => 'decline',
                        'title' => 'editor.submission.decision.decline',
                    ],
                ];
            }
            if ($submission->getStatus() == PKPSubmission::STATUS_DECLINED) {
                $decisions = $decisions + [
                    self::SUBMISSION_EDITOR_DECISION_REVERT_DECLINE => [
                        'name' => 'revert',
                        'operation' => 'revertDecline',
                        'title' => 'editor.submission.decision.revertDecline',
                    ],
                ];
            }
        }
        return $decisions;
    }

    /**
     * Define and return editor decisions for the review stage.
     * If the user cannot make decisions i.e. if it is a recommendOnly user,
     * there will be no decisions options in the review stage.
     *
     * @param $context Context
     * @param $makeDecision boolean If the user can make decisions
     *
     * @return array
     */
    protected function _externalReviewStageDecisions($context, $submission, $makeDecision = true)
    {
        $decisions = $this->_internalReviewStageDecisions($context, $submission, $makeDecision);
        unset($decisions[self::SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW]);
        return $decisions;
    }

    /**
     * @copydoc PKPEditorDecisionActionsManager::getStageNotifications()
     *
     * @return array
     */
    public function getStageNotifications()
    {
        return parent::getStageNotifications() + [
            NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW
        ];
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\workflow\EditorDecisionActionsManager', '\EditorDecisionActionsManager');
    foreach ([
        'SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW',
        'SUBMISSION_EDITOR_DECISION_ACCEPT',
        'SUBMISSION_EDITOR_DECISION_DECLINE',
        'SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW',
        'SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS',
        'SUBMISSION_EDITOR_DECISION_RESUBMIT',
        'SUBMISSION_EDITOR_DECISION_NEW_ROUND',
        'SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW',
        'SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION',
    ] as $constantName) {
        define($constantName, constant('\EditorDecisionActionsManager::' . $constantName));
    }
}
