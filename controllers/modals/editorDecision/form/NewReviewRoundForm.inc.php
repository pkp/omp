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
	function NewReviewRoundForm(&$seriesEditorSubmission, $decision = SUBMISSION_EDITOR_DECISION_RESUBMIT, $stageId = null, &$reviewRound) {
		parent::EditorDecisionForm($seriesEditorSubmission, $stageId, 'controllers/modals/editorDecision/form/newReviewRoundForm.tpl', $reviewRound);
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

		// Get this form decision actions labels.
		$actionLabels = EditorDecisionActionsManager::getActionLabels($this->_getDecisions());

		// Record the decision.
		$reviewRound =& $this->getReviewRound();
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT, $actionLabels, $reviewRound);

		// Create a new review round.
		$newRound = $this->_initiateReviewRound(
			$seriesEditorSubmission, $seriesEditorSubmission->getStageId(),
			$request, REVIEW_ROUND_STATUS_PENDING_REVIEWERS
		);

		return $newRound;
	}

	//
	// Private functions
	//
	/**
	 * Get this form decisions.
	 * @return array
	 */
	function _getDecisions() {
		return array(
			SUBMISSION_EDITOR_DECISION_RESUBMIT
		);
	}
}

?>
