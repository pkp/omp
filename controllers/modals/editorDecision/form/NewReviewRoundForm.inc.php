<?php

/**
 * @file controllers/modals/editorDecision/form/NewReviewRoundForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewReviewRoundForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating a new review round (after the first)
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');
import('monograph.reviewRound.ReviewRound');

class NewReviewRoundForm extends EditorDecisionForm {

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function NewReviewRoundForm($seriesEditorSubmission) {
		parent::EditorDecisionForm($seriesEditorSubmission, 'controllers/modals/editorDecision/form/newReviewRoundForm.tpl');
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
		SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT);

		// Create a new review round.
		$newRound = $seriesEditorSubmission->getCurrentRound() + 1;
		$this->_initiateReviewRound($seriesEditorSubmission, $seriesEditorSubmission->getCurrentReviewType(),
				$newRound, 1, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);
		return $newRound;
	}
}

?>
