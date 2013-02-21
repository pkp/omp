<?php

/**
 * @file controllers/modals/editorDecision/form/InitiateReviewForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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
	function InitiateReviewForm($seriesEditorSubmission, $decision, $stageId, $template) {
		parent::EditorDecisionForm($seriesEditorSubmission, $decision, $stageId, $template);
	}

	/**
	 * Get the stage ID constant for the submission to be moved to.
	 * @return int WORKFLOW_STAGE_ID_...
	 */
	function _getStageId() {
		assert(false); // Subclasses should override.
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
		$actionLabels = EditorDecisionActionsManager::getActionLabels(array($this->_decision));
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, $this->_decision, $actionLabels);

		// Move to the internal review stage.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, $this->_getStageId(), $request);

		// Create an initial internal review round.
		$this->_initiateReviewRound($seriesEditorSubmission, $this->_getStageId(), $request, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);
	}
}

?>
