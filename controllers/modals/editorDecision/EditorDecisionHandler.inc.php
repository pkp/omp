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
				array('decision', 'saveDecision', 'importPeerReviews', 'resubmit', 'saveResubmit', 'newReviewRound', 'saveNewReviewRound')));
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Start a new review round
	 * @return JSON
	 */
	function newReviewRound(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$decision = $request->getUserVar('decision');

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
	function saveNewReviewRound(&$args, &$request) {
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
	 * FIXME: add method doc
	 */
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


	/**
	 * Display the 'resubmit for review' form, moving the review to the next round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function resubmit(&$args, &$request) {
		// FIXME: add validation
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.editorDecision.form.ResubmitForReviewForm');
		$resubmitForReviewForm = new ResubmitForReviewForm($monographId);
		$resubmitForReviewForm->initData($args, $request);

		$json = new JSON('true', $resubmitForReviewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the resubmit for review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveResubmit(&$args, &$request) {
		// FIXME: add validation
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.editorDecision.form.ResubmitForReviewForm');
		$resubmitForReviewForm = new ResubmitForReviewForm($monographId);


		$resubmitForReviewForm->readInputData();
		if ($resubmitForReviewForm->validate()) {
			$resubmitForReviewForm->execute($args, $request);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}
}
?>