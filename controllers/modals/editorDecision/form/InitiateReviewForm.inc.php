<?php

/**
 * @file controllers/modals/editorDecision/form/InitiateReviewForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	function InitiateReviewForm($seriesEditorSubmission, $decision, $stageId) {
		parent::EditorDecisionForm($seriesEditorSubmission, $stageId, 'controllers/modals/editorDecision/form/initiateReviewForm.tpl');
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

		// Record the decision.
		import('classes.workflow.EditorDecisionActionsManager');
		$actionLabels = EditorDecisionActionsManager::getActionLabels(array(SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW));
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW, $actionLabels);

		// Move to the internal review stage.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_INTERNAL_REVIEW, $request);

		// Create an initial internal review round.
		$this->_initiateReviewRound($seriesEditorSubmission, WORKFLOW_STAGE_ID_INTERNAL_REVIEW, $request, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);
	}
}

?>
