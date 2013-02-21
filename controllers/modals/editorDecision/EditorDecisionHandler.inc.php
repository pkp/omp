<?php

/**
 * @file controllers/modals/editorDecision/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class EditorDecisionHandler extends Handler {
	/**
	 * Constructor.
	 */
	function EditorDecisionHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array_merge(array(
				'internalReview', 'saveInternalReview',
				'externalReview', 'saveExternalReview',
				'sendReviews', 'saveSendReviews',
				'promote', 'savePromote',
				'approveProofs', 'saveApproveProof'
			), $this->_getReviewRoundOps())
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

		// Some operations need a review round id in request.
		$reviewRoundOps = $this->_getReviewRoundOps();
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args, 'reviewRoundId', $reviewRoundOps));

		// Approve proof need monograph access policy.
		$router =& $request->getRouter();
		if ($router->getRequestedOp($request) == 'saveApproveProof') {
			import('classes.security.authorization.OmpMonographFileAccessPolicy');
			$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, MONOGRAPH_FILE_ACCESS_MODIFY));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_OMP_EDITOR,
			LOCALE_COMPONENT_PKP_SUBMISSION
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

		return $this->_saveEditorDecision($args, $request, 'NewReviewRoundForm', $redirectOp, SUBMISSION_EDITOR_DECISION_RESUBMIT);
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function internalReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'InitiateInternalReviewForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveInternalReview($args, &$request) {
		assert($this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE) == WORKFLOW_STAGE_ID_SUBMISSION);
		return $this->_saveEditorDecision(
			$args, $request, 'InitiateInternalReviewForm',
			WORKFLOW_STAGE_PATH_INTERNAL_REVIEW,
			SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW
		);
	}

	/**
	 * Jump from submission to external review
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function externalReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'InitiateExternalReviewForm');
	}

	/**
	 * Start a new review round in external review, bypassing internal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveExternalReview($args, &$request) {
		assert($this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE) == WORKFLOW_STAGE_ID_SUBMISSION);
		return $this->_saveEditorDecision(
			$args, $request, 'InitiateExternalReviewForm',
			WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW,
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW
		);
	}

	/**
	 * Show a save review form (responsible for decline submission modals when not in review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviews($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Show a save review form (responsible for request revisions,
	 * resubmit for review, and decline submission modals in review stages).
	 * We need this because the authorization in review stages is different
	 * when not in review stages (need to authorize review round id).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviewsInReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form when user is not in review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviews($args, &$request) {
		return $this->_saveEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form when user is in review stages.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviewsInReview($args, &$request) {
		return $this->_saveEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Show a promote form (responsible for accept submission modals outside review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promote($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Show a promote form (responsible for external review and accept submission modals
	 * in review stages). We need this because the authorization for promoting in review
	 * stages is different when not in review stages (need to authorize review round id).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promoteInReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromote($args, &$request) {
		return $this->_saveGeneralPromote($args, $request);
	}

	/**
	 * Save the send review form (same case of the
	 * promoteInReview() method, see description there).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromoteInReview($args, &$request) {
		return $this->_saveGeneralPromote($args, $request);
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

		// Retrieve the current review round.
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);

		// Retrieve peer reviews.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$peerReviews = $seriesEditorAction->getPeerReviews($seriesEditorSubmission, $reviewRound->getId());

		if(empty($peerReviews)) {
			$json = new JSONMessage(false, __('editor.review.noReviews'));
		} else {
			$json = new JSONMessage(true, $peerReviews);
		}
		return $json->getString();
	}

	/**
	 * Fetch the proofs grid handler.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function approveProofs($args, &$request) {
		$this->setupTemplate();
		$press =& $request->getPress();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormatId = $request->getUserVar('publicationFormatId');
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */

		$publicationFormat = $publicationFormatDao->getById($publicationFormatId, $monograph->getId(), $press->getId());
		if (!is_a($publicationFormat, 'PublicationFormat')) {
			fatalError('Invalid publication format id!');
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('publicationFormat', $publicationFormat);
		$templateMgr->assign_by_ref('monograph', $monograph);

		return $templateMgr->fetchJson('controllers/modals/editorDecision/approveProofs.tpl');
	}

	/**
	 * Approve a proof monograph file.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveApproveProof($args, &$request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILE);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Make sure we only alter files associated with a publication format.
		if ($monographFile->getAssocType() !== ASSOC_TYPE_PUBLICATION_FORMAT) {
			fatalError('The requested file is not associated with any publication format.');
		}
		if ($monographFile->getViewable()) {

			// No longer expose the file to readers.
			$monographFile->setViewable(false);
		} else {

			// Expose the file to readers (e.g. via e-commerce).
			$monographFile->setViewable(true);

			// Log the approve proof event.
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry'); // constants
			$user =& $request->getUser();

			$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
			$publicationFormat =& $publicationFormatDao->getById($monographFile->getAssocId(), $monograph->getId());

			MonographLog::logEvent($request, $monograph, MONOGRAPH_LOG_PROOFS_APPROVED, 'submission.event.proofsApproved', array('formatName' => $publicationFormat->getLocalizedName(),'name' => $user->getFullName(), 'username' => $user->getUsername()));
		}

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->updateObject($monographFile);

		// update the monograph's file index
		import('classes.search.MonographSearchIndex');
		MonographSearchIndex::clearMonographFiles($monograph);
		MonographSearchIndex::indexMonographFiles($monograph);

		return DAO::getDataChangedEvent($monographFile->getId());
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
		// Retrieve the decision
		$decision = (int)$request->getUserVar('decision');

		// Form handling
		$editorDecisionForm = $this->_getEditorDecisionForm($formName, $decision);
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
		// Retrieve the decision
		if (is_null($decision)) {
			$decision = (int)$request->getUserVar('decision');
		}

		$editorDecisionForm = $this->_getEditorDecisionForm($formName, $decision);
		$editorDecisionForm->readInputData();
		if ($editorDecisionForm->validate()) {
			$editorDecisionForm->execute($args, $request);

			$notificationMgr = new NotificationManager();
			$notificationMgr->updateEditorDecisionNotification($monograph, $decision, $request);

			// Update pending revisions task notifications.
			$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
			$notificationMgr->updatePendingRevisionsNotification($request, $monograph, $stageId, $decision);

			// Update "all reviews in" notification.
			$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
			if ($reviewRound) {
				$notificationMgr->updateAllReviewsInNotification($request, $reviewRound);
				$notificationMgr->deleteAllRevisionsInNotification($request, $reviewRound);
			}

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

	/**
	 * Get operations that need a review round id policy.
	 * @return array
	 */
	function _getReviewRoundOps() {
		return array('promoteInReview', 'savePromoteInReview', 'newReviewRound', 'saveNewReviewRound', 'sendReviewsInReview', 'saveSendReviewsInReview', 'importPeerReviews');
	}

	/**
	 * Get an instance of an editor decision form.
	 * @param $formName string
	 * @param $decision int
	 * @return EditorDecisionForm
	 */
	function _getEditorDecisionForm($formName, $decision) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// Retrieve the stage id
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		import("controllers.modals.editorDecision.form.$formName");
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
			$editorDecisionForm = new $formName($monograph, $decision, $stageId, $reviewRound);
			// We need a different save operation in review stages to authorize
			// the review round object.
			if (is_a($editorDecisionForm, 'PromoteForm')) {
				$editorDecisionForm->setSaveFormOperation('savePromoteInReview');
			} else if (is_a($editorDecisionForm, 'SendReviewsForm')) {
				$editorDecisionForm->setSaveFormOperation('saveSendReviewsInReview');
			}
		} else {
			$editorDecisionForm = new $formName($monograph, $decision, $stageId);
		}

		if (is_a($editorDecisionForm, $formName)) {
			return $editorDecisionForm;
		} else {
			assert(false);
			return null;
		}
	}

	function _saveGeneralPromote($args, &$request) {
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

		// Make sure user has access to the workflow stage.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$redirectWorkflowStage = $userGroupDao->getIdFromPath($redirectOp);
		$userAccessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);
		if (!array_key_exists($redirectWorkflowStage, $userAccessibleWorkflowStages)) {
			$redirectOp = null;
		}

		return $this->_saveEditorDecision($args, $request, 'PromoteForm', $redirectOp);
	}
}

?>
