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
		// FIXME: Please correctly distribute the operations among roles.
		$this->addRoleAssignment(ROLE_ID_AUTHOR,
				$authorOperations = array());
		$this->addRoleAssignment(ROLE_ID_PRESS_ASSISTANT,
				$pressAssistantOperations = array_merge($authorOperations, array()));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($pressAssistantOperations,
				array('newReviewRound', 'saveNewReviewRound', 'initiateReview', 'saveInitiateReview', 'sendReviews', 'saveSendReviews', 'promote', 'savePromote', 'importPeerReviews')));
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_SUBMISSION));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Start a new review round
	 * @return JSON
	 */
	function newReviewRound($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.editorDecision.form.NewReviewRoundForm');
		$newReviewRoundForm = new NewReviewRoundForm($monographId);
		$newReviewRoundForm->initData($args, $request);

		$json = new JSON('true', $newReviewRoundForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Start a new review round
	 * @return JSON
	 */
	function saveNewReviewRound($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		// Form handling
		import('controllers.modals.editorDecision.form.NewReviewRoundForm');
		$newReviewRoundForm = new NewReviewRoundForm($monographId);

		$newReviewRoundForm->readInputData();
		if ($newReviewRoundForm->validate()) {
			$round = $newReviewRoundForm->execute($args, $request);

			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();
			$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monographId, $round));


			$additionalAttributes = array('script' => "$(\"<li class='ui-state-default ui-corner-top ui-state-active'><a href='"
				. $url . "'>"
				. Locale::translate('submission.round', array('round' => $round)) . "</a></li>\").insertBefore('#newRoundTabContainer');"
			);

			$json = new JSON('true', null, 'true', null, $additionalAttributes);
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Start a new review round
	 * @return JSON
	 */
	function initiateReview($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.editorDecision.form.InitiateReviewForm');
		$initiateReviewForm = new InitiateReviewForm($monographId);
		$initiateReviewForm->initData($args, $request);

		$json = new JSON('true', $initiateReviewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Start a new review round
	 * @return JSON
	 */
	function saveInitiateReview($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		// Form handling
		import('controllers.modals.editorDecision.form.InitiateReviewForm');
		$initiateReviewForm = new InitiateReviewForm($monographId);

		$initiateReviewForm->readInputData();
		if ($initiateReviewForm->validate()) {
			$initiateReviewForm->execute($args, $request);

			$dispatcher =& $this->getDispatcher();
			$json = new JSON('true', $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monographId, 1)));
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Show a save review form (responsible for request revisions, resubmit for review, and decline submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function sendReviews($args, &$request) {
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
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveSendReviews($args, &$request) {
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
	 * Show a promote form (responsible for external review and accept submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function promote($args, &$request) {
		// FIXME: add validation
		$monographId = $request->getUserVar('monographId');
		$decision = $request->getUserVar('decision');

		// Form handling
		import('controllers.modals.editorDecision.form.PromoteForm');
		$promoteForm = new PromoteForm($monographId, $decision);
		$promoteForm->initData($args, $request);

		$json = new JSON('true', $promoteForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function savePromote($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$decision = $request->getUserVar('decision');

		import('controllers.modals.editorDecision.form.PromoteForm');
		$promoteForm = new PromoteForm($monographId, $decision);

		$promoteForm->readInputData();
		if ($promoteForm->validate()) {
			$promoteForm->execute($args, $request);

			if ($decision == SUBMISSION_EDITOR_DECISION_ACCEPT) {
				$dispatcher =& $this->getDispatcher();
				$json = new JSON('true', $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'copyediting', array($monographId)));
			} else {
				$json = new JSON('true');
			}
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
	function importPeerReviews($args, &$request) {
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

	/**
	 * Promote the submission into the production stage
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function sendToProduction(&$args, &$request) {
		// FIXME #5898 : Implement -- Is this just a confirm dialog or a modal?
		$monographId = $request->getUserVar('monographId');

		$json = new JSON('true');
		return $json->getString();
	}
}
?>