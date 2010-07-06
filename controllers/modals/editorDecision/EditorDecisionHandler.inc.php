<?php

/**
 * @file controllers/modals/editorDecisions/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionHandler
 * @ingroup controllers_modals_editorDecision
 *
 * @brief Handle requests for editors to make a decision
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class EditorDecisionHandler extends Handler {
	/**
	 * Constructor.
	 */
	function EditorDecisionHandler() {
		parent::Handler();
	}

	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array('sendReviews', 'requestRevisions', 'resubmit', 'decision', 'saveDecision', 'importPeerReviews');
	}

	function decision(&$args, &$request) {
		// FIXME: add validation
		$monographId = $request->getUserVar('monographId');
		$decision = $request->getUserVar('decision');

		// Form handling
		import('controllers.modals.editorDecision.form.SendReviewsForm');
		$sendReviewsForm = new SendReviewsForm($monographId, $decision);
		$sendReviewsForm->initData($args, $request);

		$json = new JSON('true', $sendReviewsForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the submission decline modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveDecision(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$decision = $request->getUserVar('decision');

		import('controllers.modals.editorDecision.form.SendReviewsForm');
		$sendReviewsForm = new SendReviewsForm($monographId, $decision);

		$sendReviewsForm->readInputData();
		if ($sendReviewsForm->validate()) {
			$sendReviewsForm->execute($args, $request);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Import all free-text/review form reviews to paste into message
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function importPeerReviews(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$peerReviews = SeriesEditorAction::getPeerReviews($seriesEditorSubmission);

		if(empty($peerReviews)) {
			$json = new JSON('false', Locale::translate('editor.review.noReviews'));
		} else {
			$json = new JSON('true', $peerReviews);
		}
		return $json->getString();
	}
}
?>