<?php

/**
 * @file classes/workflow/EditorDecisionActionsManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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

// Editorial stage decision actions.
define('SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION', 7);

class EditorDecisionActionsManager {

	/**
	 * Get decision actions labels.
	 * @param $decisions
	 * @return array
	 */
	function getActionLabels($decisions) {
		$allDecisionsData = array();
		$allDecisionsData =
			EditorDecisionActionsManager::_submissionStageDecisions() +
			EditorDecisionActionsManager::_internalReviewStageDecisions() +
			EditorDecisionActionsManager::_externalReviewStageDecisions() +
			EditorDecisionActionsManager::_editorialStageDecisions();

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
	function getEditorTakenActionInReviewRound($reviewRound, $decisions = array()) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$editorDecisions = $seriesEditorSubmissionDao->getEditorDecisions($reviewRound->getSubmissionId(), $reviewRound->getStageId(), $reviewRound->getRound());

		if (empty($decisions)) {
			$decisions = array_keys(EditorDecisionActionsManager::_internalReviewStageDecisions());
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
	 * Get the available decisions by stage ID.
	 * @param $stageId int WORKFLOW_STAGE_ID_...
	 */
	function getStageDecisions($stageId) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				return EditorDecisionActionsManager::_submissionStageDecisions();
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return EditorDecisionActionsManager::_internalReviewStageDecisions();
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				return EditorDecisionActionsManager::_externalReviewStageDecisions();
			case WORKFLOW_STAGE_ID_EDITING:
				return EditorDecisionActionsManager::_editorialStageDecisions();
			default:
				assert(false);
		}
	}

	//
	// Private helper methods.
	//
	/**
	 * Define and return editor decisions for the submission stage.
	 * @return array
	 */
	function _submissionStageDecisions() {
		static $decisions = array(
			SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW => array(
				'name' => 'internalReview',
				'operation' => 'internalReview',
				'title' => 'editor.monograph.decision.sendInternalReview',
				'image' => 'advance',
				'titleIcon' => 'modal_review',
			),
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array(
				'operation' => 'externalReview',
				'name' => 'externalReview',
				'title' => 'editor.monograph.decision.sendExternalReview',
				'image' => 'advance',
				'titleIcon' => 'modal_review',
			),
			SUBMISSION_EDITOR_DECISION_ACCEPT => array(
				'name' => 'accept',
				'operation' => 'promote',
				'title' => 'editor.monograph.decision.accept',
				'image' => 'promote',
				'help' => 'editor.review.NotifyAuthorAccept',
				'titleIcon' => '',
			),
			SUBMISSION_EDITOR_DECISION_DECLINE => array(
				'name' => 'decline',
				'operation' => 'sendReviews',
				'title' => 'editor.monograph.decision.decline',
				'image' => 'decline',
				'help' => 'editor.review.NotifyAuthorDecline',
				'titleIcon' => '',
			),
		);

		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * @return array
	 */
	function _internalReviewStageDecisions() {
		static $decisions = array(
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array(
				'operation' => 'sendReviewsInReview',
				'name' => 'requestRevisions',
				'title' => 'editor.monograph.decision.requestRevisions',
				'image' => 'revisions',
				'help' => 'editor.review.NotifyAuthorRevisions',
				'titleIcon' => '',
			),
			SUBMISSION_EDITOR_DECISION_RESUBMIT => array(
				'operation' => 'sendReviewsInReview',
				'name' => 'resubmit',
				'title' => 'editor.monograph.decision.resubmit',
				'image' => 'resubmit',
				'help' => 'editor.review.NotifyAuthorResubmit',
				'titleIcon' => '',
			),
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array(
				'operation' => 'promoteInReview',
				'name' => 'externalReview',
				'title' => 'editor.monograph.decision.sendExternalReview',
				'image' => 'advance',
				'help' => 'editor.review.NotifyAuthorExternal',
				'titleIcon' => 'modal_review',
			),
			SUBMISSION_EDITOR_DECISION_ACCEPT => array(
				'operation' => 'promoteInReview',
				'name' => 'accept',
				'title' => 'editor.monograph.decision.accept',
				'image' => 'promote',
				'help' => 'editor.review.NotifyAuthorAccept',
				'titleIcon' => '',
			),
			SUBMISSION_EDITOR_DECISION_DECLINE => array(
				'operation' => 'sendReviewsInReview',
				'name' => 'decline',
				'title' => 'editor.monograph.decision.decline',
				'image' => 'decline',
				'help' => 'editor.review.NotifyAuthorDecline',
				'titleIcon' => '',
			),
		);

		return $decisions;
	}

	/**
	 * Define and return editor decisions for the review stage.
	 * @return array
	 */
	function _externalReviewStageDecisions() {
		$decisions = EditorDecisionActionsManager::_internalReviewStageDecisions();
		unset($decisions[SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW]);
		return $decisions;
	}


	/**
	 * Define and return editor decisions for the editorial stage.
	 * @return array
	 */
	function _editorialStageDecisions() {
		static $decisions = array(
			SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION => array(
				'operation' => 'promote',
				'name' => 'sendToProduction',
				'title' => 'editor.monograph.decision.sendToProduction',
				'image' => 'send_production',
				'titleIcon' => 'modal_send_to_production',
			),
		);

		return $decisions;
	}
}

?>
