<?php

/**
 * @file controllers/modals/editorDecision/form/PromoteForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 * @param $stageId int
	 */
	function PromoteForm($seriesEditorSubmission, $decision, $stageId) {
		if (!in_array(
			$decision,
			array(SUBMISSION_EDITOR_DECISION_ACCEPT,
				 SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW,
				 SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION)
		)) {
			fatalError('Invalid decision!');
		}

		parent::EditorDecisionWithEmailForm(
			$seriesEditorSubmission, $decision, $stageId,
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
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept',
			SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION => 'editor.monograph.decision.sendToProduction'
		);

		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();
		$this->setData('stageId', $seriesEditorSubmission->getStageId());

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
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, $decision);

		// Identify email key and status of round.
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				$status = REVIEW_ROUND_STATUS_ACCEPTED;

				// Move to the editing stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EDITING);

				// Bring in the MONOGRAPH_FILE_* constants.
				import('classes.monograph.MonographFile');
				// Bring in the Manager (we need it).
				import('classes.file.MonographFileManager');
				foreach (array('selectedFiles', 'selectedAttachments') as $userVar) {
					$selectedFiles = $this->getData($userVar);
					if(is_array($selectedFiles)) {
						foreach ($selectedFiles as $selectedFile) {
							// Split the file into file id and file revision.
							list($fileId, $revision) = explode('-', $selectedFile);
							MonographFileManager::copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_FINAL);
						}
					}
				}
				// Send email to the author.
				$this->_sendReviewMailToAuthor($seriesEditorSubmission, $status, $emailKey, $request);
				break;

			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				$emailKey = 'EDITOR_DECISION_SEND_TO_EXTERNAL';
				$status = REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL;

				// Move to the external review stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);

				// Create an initial external review round.
				$this->_initiateReviewRound($seriesEditorSubmission, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, 1);

				// Send email to the author.
				$this->_sendReviewMailToAuthor($seriesEditorSubmission, $status, $emailKey, $request);
				break;
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				$emailKey = 'EDITOR_DECISION_SEND_TO_PRODUCTION';

				// FIXME: this is copy-pasted from above, save the FILE_GALLEY.
				// Also need a way to bring over all files

				// Move to the editing stage.
				$seriesEditorAction->incrementWorkflowStage($seriesEditorSubmission, WORKFLOW_STAGE_ID_PRODUCTION);

				// Bring in the MONOGRAPH_FILE_* constants.
				import('classes.monograph.MonographFile');
				// Bring in the Manager (we need it).
				import('classes.file.MonographFileManager');
				foreach (array('selectedFiles', 'selectedAttachments') as $userVar) {
					$selectedFiles = $this->getData($userVar);
					if(is_array($selectedFiles)) {
						foreach ($selectedFiles as $selectedFile) {
							// Split the file into file id and file revision.
							list($fileId, $revision) = explode('-', $selectedFile);
							MonographFileManager::copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_GALLEY);
						}
					}
				}

				// FIXME? No email to author?
				break;
			default:
				fatalError('Unsupported decision!');
		}
	}
}

?>
