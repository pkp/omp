<?php

/**
 * @file controllers/modals/editorDecision/form/PromoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PromoteForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form for promoting a submission (to external review or editing)
 */

import('controllers.modals.editorDecision.form.EditorDecisionWithEmailForm');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class PromoteForm extends EditorDecisionWithEmailForm {

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision int
	 * @param $stageId int
	 * @param $reviewRound ReviewRound
	 */
	function PromoteForm(&$seriesEditorSubmission, $decision, $stageId, $reviewRound = null) {
		if (!in_array($decision, $this->_getDecisions())) {
			fatalError('Invalid decision!');
		}

		$this->setSaveFormOperation('savePromote');

		parent::EditorDecisionWithEmailForm(
			$seriesEditorSubmission, $decision, $stageId,
			'controllers/modals/editorDecision/form/promoteForm.tpl',
			$reviewRound
		);
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		$actionLabels = EditorDecisionActionsManager::getActionLabels($this->_getDecisions());

		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();
		$this->setData('stageId', $this->getStageId());

		return parent::initData($args, $request, $actionLabels);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Retrieve the submission.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Get this form decision actions labels.
		$actionLabels = EditorDecisionActionsManager::getActionLabels($this->_getDecisions());

		// Record the decision.
		$reviewRound = $this->getReviewRound();
		$decision = $this->getDecision();
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, $decision, $actionLabels, $reviewRound);

		// Identify email key and status of round.
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($seriesEditorSubmission->getPressId(), $seriesEditorSubmission->getId());
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				$status = REVIEW_ROUND_STATUS_ACCEPTED;

				$this->_updateReviewRoundStatus($seriesEditorSubmission, $status, $reviewRound);

				// Move to the editing stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EDITING, $request);

				// Bring in the MONOGRAPH_FILE_* constants.
				import('classes.monograph.MonographFile');
				// Bring in the Manager (we need it).
				import('classes.file.MonographFileManager');

				$selectedFiles = $this->getData('selectedFiles');
				if(is_array($selectedFiles)) {
					foreach ($selectedFiles as $selectedFile) {
						// Split the file into file id and file revision.
						list($fileId, $revision) = explode('-', $selectedFile);
						$monographFileManager->copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_FINAL, null, true);
					}
				}

				// Send email to the author.
				$this->_sendReviewMailToAuthor($seriesEditorSubmission, $emailKey, $request);
				break;

			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				$emailKey = 'EDITOR_DECISION_SEND_TO_EXTERNAL';
				$status = REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL;

				$this->_updateReviewRoundStatus($seriesEditorSubmission, $status, $reviewRound);

				// Move to the external review stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, $request);

				// Create an initial external review round.
				$this->_initiateReviewRound($seriesEditorSubmission, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, $request, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);

				// Send email to the author.
				$this->_sendReviewMailToAuthor($seriesEditorSubmission, $emailKey, $request);
				break;
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				// FIXME: this is copy-pasted from above, save the FILE_GALLEY.

				// Move to the editing stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_PRODUCTION, $request);

				// Bring in the MONOGRAPH_FILE_* constants.
				import('classes.monograph.MonographFile');
				// Bring in the Manager (we need it).
				import('classes.file.MonographFileManager');

				// Move the revisions to the next stage
				$selectedFiles = $this->getData('selectedFiles');
				if(is_array($selectedFiles)) {
					foreach ($selectedFiles as $selectedFile) {
						// Split the file into file id and file revision.
						list($fileId, $revision) = explode('-', $selectedFile);
						$monographFileManager->copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_PRODUCTION_READY);
					}
				}
				break;
			default:
				fatalError('Unsupported decision!');
		}
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
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW,
			SUBMISSION_EDITOR_DECISION_ACCEPT,
			SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION
		);
	}
}

?>
