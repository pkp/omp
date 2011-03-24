<?php

/**
 * @file controllers/modals/editorDecision/form/PromoteForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PromoteForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form for promoting a submission (to external review or editing)
 */

import('controllers.modals.editorDecision.form.EditorDecisionWithEmailForm');

import('classes.submission.common.Action');

class PromoteForm extends EditorDecisionWithEmailForm {

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision int
	 */
	function PromoteForm($seriesEditorSubmission, $decision) {
		assert(
			in_array(
				$decision,
				array(SUBMISSION_EDITOR_DECISION_ACCEPT, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW)
			)
		);

		parent::EditorDecisionWithEmailForm(
			$seriesEditorSubmission, $decision,
			'controllers/modals/editorDecision/form/promoteForm.tpl'
		);
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		$actionLabels = array(
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => 'editor.monograph.decision.externalReview',
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept'
		);

		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();
		$this->setData('stageId', $seriesEditorSubmission->getCurrentStageId());

		return parent::initData($args, $request, $actionLabels);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Retrieve the submission.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Record the decision.
		$decision = $this->getDecision();
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($seriesEditorSubmission, $decision);

		// Identify email key and status of round.
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				$status = REVIEW_ROUND_STATUS_ACCEPTED;

				// Move to the editing stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EDITING);
				break;

			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				// FIXME #6123: will we have an email key for this decision?
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				$status = REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL;

				// Move to the external review stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);

				// Create an initial external review round.
				$this->_initiateReviewRound($seriesEditorSubmission, REVIEW_TYPE_EXTERNAL, 1);
				break;

			default:
				// Unsupported decision.
				assert(false);
		}

		// Send email to the author.
		$this->_sendReviewMailToAuthor($seriesEditorSubmission, $status, $emailKey, $request);
	}
}

?>
