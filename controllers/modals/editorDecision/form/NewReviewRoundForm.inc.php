<?php

/**
 * @file controllers/modals/editorDecision/form/NewReviewRoundForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewReviewRoundForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating a new review round (after the first)
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');
import('classes.monograph.reviewRound.ReviewRound');

class NewReviewRoundForm extends EditorDecisionForm {

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision int
	 * @param stageid int
	 */
	function NewReviewRoundForm($seriesEditorSubmission, $decision = SUBMISSION_EDITOR_DECISION_RESUBMIT, $stageId = null) {
		parent::EditorDecisionForm($seriesEditorSubmission, $stageId, 'controllers/modals/editorDecision/form/newReviewRoundForm.tpl');
		// WARNING: this constructor may be invoked dynamically by
		// EditorDecisionHandler::_instantiateEditorDecision.
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::execute()
	 * @return integer The new review round number
	 */
	function execute($args, &$request) {
		// Retrieve the submission.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Record the decision.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT, $this->getDecisionLabels());

		// Create a new review round.
		$newRound = $seriesEditorSubmission->getCurrentRound() + 1;
		$this->_initiateReviewRound(
			$seriesEditorSubmission, $seriesEditorSubmission->getStageId(),
			$newRound, REVIEW_ROUND_STATUS_PENDING_REVIEWERS
		);
		return $newRound;
	}

	//
	// Private functions
	//
	/**
	 * Get the associative array of decisions to decision label locale keys.
	 * @return array
	 */
	function getDecisionLabels() {
		return array(
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept',
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.pendingRevisions',
			SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => 'editor.monograph.decision.externalReview',
			SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline',
			SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION => 'editor.monograph.decision.sendToProduction'
		);
	}
}

?>
