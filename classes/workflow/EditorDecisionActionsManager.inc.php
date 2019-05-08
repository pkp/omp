<?php

/**
 * @file classes/workflow/EditorDecisionActionsManager.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionActionsManager
 * @ingroup classes_workflow
 *
 * @brief Wrapper class for create and assign editor decisions actions to template manager.
 */

// Submission stage decision actions.
define('SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW', 1);

// Submission and review stages decision actions.
define('SUBMISSION_EDITOR_DECISION_ACCEPT', 2);
define('SUBMISSION_EDITOR_DECISION_DECLINE', 6);

// Review stage decisions actions.
define('SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW', 3);
define('SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS', 4);
define('SUBMISSION_EDITOR_DECISION_RESUBMIT', 5);

// Review stage recommendation actions.
define('SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW', 15);

// Editorial stage decision actions.
define('SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION', 7);

// Editorial stage decision actions.
import('lib.pkp.classes.workflow.PKPEditorDecisionActionsManager');

class EditorDecisionActionsManager extends PKPEditorDecisionActionsManager {

	/**
	 * Get decision actions labels.
	 * @param $request PKPRequest
	 * @param $stageId int
	 * @param $decisions array
	 * @return array
	 */
	function getActionLabels($request, $stageId, $decisions) {
		$allDecisionsData =
			$this->_submissionStageDecisions($stageId) +
			$this->_internalReviewStageDecisions() +
			$this->_externalReviewStageDecisions($request) +
			$this->_editorialStageDecisions();

		$actionLabels = array();
		foreach($decisions as $decision) {
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
	 * @param $reviewRound ReviewRound
	 * @param $decisions array
	 * @return boolean
	 */
	public function getEditorTakenActionInReviewRound($reviewRound, $decisions = array()) {
		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
		$editorDecisions = $editDecisionDao->getEditorDecisions($reviewRound->getSubmissionId(), $reviewRound->getStageId(), $reviewRound->getRound());

		if (empty($decisions)) {
			$decisions = array_keys($this->_internalReviewStageDecisions());
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
	public  function getStageDecisions($request, $stageId, $makeDecision = true) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return $this->_internalReviewStageDecisions($makeDecision);
		}
		return parent::getStageDecisions($request, $stageId, $makeDecision);
	}

	/**
	 * Get an associative array matching editor recommendation codes with locale strings.
	 * (Includes default '' => "Choose One" string.)
	 * @param $stageId integer
	 * @return array recommendation => localeString
	 */
	public function getRecommendationOptions($stageId) {
		$recommendationOptions = parent::getRecommendationOptions($stageId);
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$recommendationOptions[SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW] = 'editor.submission.decision.sendExternalReview';
		}
		return $recommendationOptions;
	}

	//
	// Private helper methods.
	//
	/**
	 * @copydoc PKPEditorDecisionActionsManager::_submissionStageDecisions()
	 */
	protected function _submissionStageDecisions($stageId, $makeDecision = true) {
		$decisions = parent::_submissionStageDecisions($stageId, $makeDecision);
		$decisions[SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW] = array(
			'name' => 'internalReview',
			'operation' => 'internalReview',
			'title' => 'editor.submission.decision.sendInternalReview',
		);
		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * If the user cannot make decisions i.e. if it is a recommendOnly user,
	 * there will be no decisions options in the review stage.
	 * @param $makeDecision boolean If the user can make decisions
	 * @return array
	 */
	protected function _internalReviewStageDecisions($makeDecision = true) {
		$decisions = array();
		if ($makeDecision) {
			$decisions = array(
				SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array(
					'operation' => 'sendReviewsInReview',
					'name' => 'requestRevisions',
					'title' => 'editor.submission.decision.requestRevisions',
				),
				SUBMISSION_EDITOR_DECISION_RESUBMIT => array(
					'name' => 'resubmit',
					'title' => 'editor.submission.decision.resubmit',
				),
				SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array(
					'operation' => 'promoteInReview',
					'name' => 'externalReview',
					'title' => 'editor.submission.decision.sendExternalReview',
					'toStage' => 'workflow.review.externalReview',
				),
				SUBMISSION_EDITOR_DECISION_ACCEPT => array(
					'operation' => 'promoteInReview',
					'name' => 'accept',
					'title' => 'editor.submission.decision.accept',
					'toStage' => 'submission.copyediting',
				),
				SUBMISSION_EDITOR_DECISION_DECLINE => array(
					'operation' => 'sendReviewsInReview',
					'name' => 'decline',
					'title' => 'editor.submission.decision.decline',
				),
			);
		}
		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * If the user cannot make decisions i.e. if it is a recommendOnly user,
	 * there will be no decisions options in the review stage.
	 * @param $request PKPRequest
	 * @param $makeDecision boolean If the user can make decisions
	 * @return array
	 */
	protected function _externalReviewStageDecisions($request, $makeDecision = true) {
		$decisions = $this->_internalReviewStageDecisions($makeDecision);
		unset($decisions[SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW]);
		return $decisions;
	}

	/**
	 * @copydoc PKPEditorDecisionActionsManager::getStageNotifications()
	 * @return array
	 */
	public function getStageNotifications() {
		return parent::getStageNotifications() + array(
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW
		);
	}
}


