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
import('lib.pkp.classes.core.JSONMessage');

// Bring in decision constants
import('classes.submission.common.Action');

class EditorDecisionHandler extends Handler {
	/**
	 * Constructor.
	 */
	function EditorDecisionHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array(
				'newReviewRound', 'saveNewReviewRound',
				'initiateReview', 'saveInitiateReview',
				'sendReviews', 'saveSendReviews',
				'promote', 'savePromote',
				'importPeerReviews',
				'approveProofs', 'saveApproveProofs'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpEditorDecisionAccessPolicy');
		$this->addPolicy(new OmpEditorDecisionAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		AppLocale::requireComponents(
			array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION)
		);
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
		return $this->_initiateEditorDecision($args, $request, 'NewReviewRoundForm');
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
		// FIXME: this can probably all be managed somewhere.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
		} elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} else {
			assert(false);
		}

		return $this->_saveEditorDecision($args, $request, 'NewReviewRoundForm', $redirectOp);
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function initiateReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'InitiateReviewForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveInitiateReview($args, &$request) {
		// FIXME: this can probably all be managed somewhere.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$decision = null;
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
			$decision = SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW;
		} elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} else {
			assert(false);
		}

		return $this->_saveEditorDecision($args, $request, 'InitiateReviewForm', $redirectOp, $decision);
	}

	/**
	 * Show a save review form (responsible for request revisions, resubmit for review, and decline submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviews($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviews($args, &$request) {
		return $this->_saveEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Show a promote form (responsible for external review and accept submission modals)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promote($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromote($args, &$request) {
		// Redirect to the next workflow page after
		// promoting the submission.
		$decision = (int)$request->getUserVar('decision');

		$redirectOp = null;

		if ($decision == SUBMISSION_EDITOR_DECISION_ACCEPT) {
			$redirectOp = WORKFLOW_STAGE_PATH_EDITING;
		} elseif ($decision == SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} elseif ($decision == SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION) {
			$redirectOp = WORKFLOW_STAGE_PATH_PRODUCTION;
		}

		return $this->_saveEditorDecision($args, $request, 'PromoteForm', $redirectOp);
	}

	/**
	 * Import all free-text/review form reviews to paste into message
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function importPeerReviews($args, &$request) {
		// Retrieve the authorized submission.
		$seriesEditorSubmission =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve peer reviews.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$peerReviews = $seriesEditorAction->getPeerReviews($seriesEditorSubmission);

		if(empty($peerReviews)) {
			$json = new JSONMessage(false, __('editor.review.noReviews'));
		} else {
			$json = new JSONMessage(true, $peerReviews);
		}
		return $json->getString();
	}

	/**
	 * Show the approve proofs modal
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function approveProofs($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		// TODO: implement init and display the ApproveProofsForm
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $stageId);

		// FIXME: setting to one for testing.
		$templateMgr->assign('publicationFormatId', 1);
		return $templateMgr->fetchJson('controllers/modals/editorDecision/form/approveProofsForm.tpl');
	}

	/**
	 * Save the approved proofs
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveApproveProofs($args, &$request) {
		// TODO: implement and execute the ApproveProofsForm
		$json = new JSONMessage(true);
		return $json->getString();
	}

	//
	// Private helper methods
	//
	/**
	 * Initiate an editor decision.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $formName string Name of form to call
	 * @return string Serialized JSON object
	 */
	function _initiateEditorDecision($args, &$request, $formName) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// Retrieve the stage id
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		// Retrieve the decision
		$decision = (int)$request->getUserVar('decision');



		// Form handling
		import("controllers.modals.editorDecision.form.$formName");
		$editorDecisionForm = new $formName($monograph, $decision, $stageId);
		$editorDecisionForm->initData($args, $request);

		$json = new JSONMessage(true, $editorDecisionForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save an editor decision.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $formName string Name of form to call
	 * @param $redirectOp string A workflow stage operation to
	 *  redirect to if successful (if any).
	 * @return string Serialized JSON object
	 */
	function _saveEditorDecision($args, &$request, $formName, $redirectOp = null, $decision = null) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// Retrieve the stage id
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		// Retrieve the decision
		if (is_null($decision)) {
			$decision = (int)$request->getUserVar('decision');
		}

		// Form handling
		import("controllers.modals.editorDecision.form.$formName");
		$editorDecisionForm = new $formName($monograph, $decision, $stageId);

		$editorDecisionForm->readInputData();
		if ($editorDecisionForm->validate()) {
			$editorDecisionForm->execute($args, $request);

			$notificationMgr = new NotificationManager();
			$notificationMgr->updateEditorDecisionNotification($monograph, $decision, $request);

			if ($redirectOp) {
				$dispatcher =& $this->getDispatcher();
				$redirectUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', $redirectOp, array($monograph->getId()));
				return $request->redirectUrlJson($redirectUrl);
			} else {
				// Needed to update review round status notifications.
				return DAO::getDataChangedEvent();
			}
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}
}

?>
