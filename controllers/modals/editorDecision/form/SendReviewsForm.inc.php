<?php

/**
 * @file controllers/modals/editorDecision/form/SendReviewsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SendReviewsForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form to request additional work from the author (Request revisions or
 *  resubmit for review), or to decline the submission.
 */

import('controllers.modals.editorDecision.form.EditorDecisionWithEmailForm');

import('classes.submission.common.Action');

class SendReviewsForm extends EditorDecisionWithEmailForm {
	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision int
	 * @param $stageId int
	 */
	function SendReviewsForm($seriesEditorSubmission, $decision, $stageId) {
		if (!in_array(
			$decision,
			array(
				SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS,
				SUBMISSION_EDITOR_DECISION_RESUBMIT,
				SUBMISSION_EDITOR_DECISION_DECLINE
			)
		)) {
			fatalError('Invalid decision!');
		}

		parent::EditorDecisionWithEmailForm(
			$seriesEditorSubmission, $decision, $stageId,
			'controllers/modals/editorDecision/form/sendReviewsForm.tpl'
		);
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		$actionLabels = $this->getDecisionLabels();

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
		$seriesEditorAction->recordDecision($request, $seriesEditorSubmission, $decision, $this->getDecisionLabels());

		// Identify email key and status of round.
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				$emailKey = 'SUBMISSION_UNSUITABLE';
				$status = REVIEW_ROUND_STATUS_REVISIONS_REQUESTED;
				break;

			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				$emailKey = 'EDITOR_DECISION_RESUBMIT';
				$status = REVIEW_ROUND_STATUS_RESUBMITTED;
				break;

			case SUBMISSION_EDITOR_DECISION_DECLINE:
				$emailKey = 'SUBMISSION_UNSUITABLE';
				$status = REVIEW_ROUND_STATUS_DECLINED;
				break;

			default:
				fatalError('Unsupported decision!');
		}

		$this->_updateReviewRoundStatus($seriesEditorSubmission, $status);
		
		// Send email to the author.
		$this->_sendReviewMailToAuthor($seriesEditorSubmission, $emailKey, $request);
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
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.requestRevisions',
			SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
			SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline'
		);
	}
}

?>
