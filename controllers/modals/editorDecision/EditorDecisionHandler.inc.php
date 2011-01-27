<?php

/**
 * @file controllers/modals/editorDecision/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('newReviewRound', 'saveNewReviewRound', 'initiateReview', 'saveInitiateReview', 'sendReviews',
						'saveSendReviews', 'promote', 'savePromote', 'importPeerReviews', 'sendToProduction'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_SUBMISSION));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler actions
	//
	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function newReviewRound($args, &$request) {
		return $this->_editorDecision($args, $request, 'NewReviewRoundForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveNewReviewRound($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		// Form handling
		import('controllers.modals.editorDecision.form.NewReviewRoundForm');
		$newReviewRoundForm = new NewReviewRoundForm($monograph);

		$newReviewRoundForm->readInputData();
		if ($newReviewRoundForm->validate()) {
			$round = $newReviewRoundForm->execute($args, $request);

			// FIXME: Sending scripts through JSON is evil. This script
			// should (and can) be moved to the client side, #see 6357.

			// Generate the new review round tab script.
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();
			$newRoundUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monograph->getId(), $round));
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('newRoundUrl', $newRoundUrl);
			$templateMgr->assign('round', $round);
			$reviewRoundTabScript = $templateMgr->fetch('controllers/modals/editorDecision/form/reviewRoundTab.tpl');

			// Create a JSON message with the script.
			$additionalAttributes = array('script' => $reviewRoundTabScript);
			$json = new JSON(true, null, true, null, $additionalAttributes);
		} else {
			$json = new JSON(false);
		}

		return $json->getString();
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function initiateReview($args, &$request) {
		return $this->_editorDecision($args, $request, 'InitiateReviewForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveInitiateReview($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		// Form handling
		import('controllers.modals.editorDecision.form.InitiateReviewForm');
		$initiateReviewForm = new InitiateReviewForm($monograph);

		$initiateReviewForm->readInputData();
		if ($initiateReviewForm->validate()) {
			$initiateReviewForm->execute($args, $request);

			$dispatcher =& $this->getDispatcher();
			$json = new JSON(true);
			$json->setEvent('redirectRequested', array($dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monograph->getId(), 1))));
		} else {
			$json = new JSON(false);
		}

		return $json->getString();
	}

	/**
	 * Show a save review form (responsible for request revisions, resubmit for review, and decline submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviews($args, &$request) {
		return $this->_editorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviews($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$decision = $request->getUserVar('decision');

		import('controllers.modals.editorDecision.form.SendReviewsForm');
		$sendReviewsForm = new SendReviewsForm($monograph, $decision);

		$sendReviewsForm->readInputData();
		if ($sendReviewsForm->validate()) {
			$sendReviewsForm->execute($args, $request);

			$json = new JSON(true);
		} else {
			$json = new JSON(false);
		}

		return $json->getString();
	}

	/**
	 * Show a promote form (responsible for external review and accept submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promote($args, &$request) {
		return $this->_editorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromote($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$decision = $request->getUserVar('decision');

		import('controllers.modals.editorDecision.form.PromoteForm');
		$promoteForm = new PromoteForm($monograph, $decision);

		$promoteForm->readInputData();
		if ($promoteForm->validate()) {
			$promoteForm->execute($args, $request);

			$json = new JSON(true);
			$dispatcher =& $this->getDispatcher();
			if ($decision == SUBMISSION_EDITOR_DECISION_ACCEPT) {
				$json->setEvent('redirectRequested', array($dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'copyediting', array($monograph->getId()))));
			} elseif ($decision == SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW) {
				$json->setEvent('redirectRequested', array($dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monograph->getId()))));
			} else {
				$json = new JSON(true);
			}
		} else {
			$json = new JSON(false);
		}

		return $json->getString();
	}

	/**
	 * Import all free-text/review form reviews to paste into message
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function importPeerReviews($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$peerReviews = SeriesEditorAction::getPeerReviews($seriesEditorSubmission);

		if(empty($peerReviews)) {
			$json = new JSON(false, Locale::translate('editor.review.noReviews'));
		} else {
			$json = new JSON(true, $peerReviews);
		}
		return $json->getString();
	}

	/**
	 * Promote the submission into the production stage
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendToProduction(&$args, &$request) {
		// Retrieve the submission.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Move to the production workflow stage
		import('classes.submission.seriesEditor.SeriesEditorAction');
		SeriesEditorAction::incrementWorkflowStage($monograph, WORKFLOW_STAGE_ID_PRODUCTION);

		$json = new JSON(true);
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Consolidates all editor decision form calls into one function
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $formName string Name of form to call
	 * @return string Serialized JSON object
	 */
	function _editorDecision($args, &$request, $formName) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// FIXME: Need to validate the decision (Does it combine with the
		// requested operation? Is it a valid decision? Is the user authorized
		// to take that decision? See #6199.
		$decision =& $request->getUserVar('decision');

		// Form handling
		import("controllers.modals.editorDecision.form.$formName");
		$editorDecisionForm = new $formName($monograph, $decision);
		$editorDecisionForm->initData($args, $request);

		$json = new JSON(true, $editorDecisionForm->fetch($request));
		return $json->getString();
	}
}
?>