<?php

/**
 * @file controllers/modals/editorDecision/form/InitiateReviewForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InitiateReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating the first review round for a submission
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

class InitiateReviewForm extends EditorDecisionForm {

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function InitiateReviewForm($seriesEditorSubmission) {
		parent::EditorDecisionForm($seriesEditorSubmission, 'controllers/modals/editorDecision/form/initiateReviewForm.tpl');
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Retrieve the submission.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Move to the internal review stage.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_INTERNAL_REVIEW);

		// Create an initial internal review round.
		$this->_initiateReviewRound($seriesEditorSubmission, REVIEW_TYPE_INTERNAL, 1, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);
	}
}

?>
