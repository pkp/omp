<?php

/**
 * @file controllers/modals/editorDecision/form/SendReviewsForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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
	 */
	function SendReviewsForm($seriesEditorSubmission, $decision) {
		assert(in_array($decision, array(SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS,
				SUBMISSION_EDITOR_DECISION_RESUBMIT, SUBMISSION_EDITOR_DECISION_DECLINE)));

		parent::EditorDecisionWithEmailForm($seriesEditorSubmission, $decision, 'controllers/modals/editorDecision/form/sendReviewsForm.tpl');
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		$actionLabels = array(
				SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.requestRevisions',
				SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
				SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline');

		return parent::initData($args, $request, $actionLabels);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Retrieve the submission.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Record the decision.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		SeriesEditorAction::recordDecision($seriesEditorSubmission, $decision);

		// Identify email key and status of round.
		$decision = $this->getDecision();
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
				// Unsupported decision.
				assert(false);
		}

		// Send email to the author.
		$this->_sendReviewMailToAuthor($seriesEditorSubmission, $status, $emailKey);
	}
}

?>
