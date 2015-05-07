<?php

/**
 * @file controllers/modals/editorDecision/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionHandler
 * @ingroup controllers_modals_editorDecision
 *
 * @brief Handle requests for editors to make a decision
 */

import('lib.pkp.classes.controllers.modals.editorDecision.PKPEditorDecisionHandler');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class EditorDecisionHandler extends PKPEditorDecisionHandler {
	/**
	 * Constructor.
	 */
	function EditorDecisionHandler() {
		parent::PKPEditorDecisionHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
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
	function authorize($request, &$args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpEditorDecisionAccessPolicy');
		$this->addPolicy(new OmpEditorDecisionAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));

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
	function saveNewReviewRound($args, $request) {
		// FIXME: this can probably all be managed somewhere.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
		} elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} else {
			$redirectOp = null; // Suppress warn
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
	function internalReview($args, $request) {
		return $this->_initiateEditorDecision($args, $request, 'InitiateInternalReviewForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveInternalReview($args, $request) {
		assert($this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE) == WORKFLOW_STAGE_ID_SUBMISSION);
		return $this->_saveEditorDecision(
			$args, $request, 'InitiateInternalReviewForm',
			WORKFLOW_STAGE_PATH_INTERNAL_REVIEW,
			SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW
		);
	}

	/**
	 * Fetch the proofs grid handler.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function approveProofs($args, $request) {
		$this->setupTemplate($request);
		$context = $request->getContext();
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$publicationFormatId = $request->getUserVar('publicationFormatId');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */

		$publicationFormat = $publicationFormatDao->getById($publicationFormatId, $submission->getId(), $context->getId());
		if (!is_a($publicationFormat, 'PublicationFormat')) {
			fatalError('Invalid publication format id!');
		}

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('representation', $publicationFormat);
		$templateMgr->assign('submission', $submission);

		return $templateMgr->fetchJson('controllers/modals/editorDecision/approveProofs.tpl');
	}

	/**
	 * Approve a proof submission file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function saveApproveProof($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		// Make sure we only alter files associated with a publication format.
		if ($submissionFile->getAssocType() !== ASSOC_TYPE_PUBLICATION_FORMAT) {
			fatalError('The requested file is not associated with any publication format.');
		}
		if ($submissionFile->getViewable()) {

			// No longer expose the file to readers.
			$submissionFile->setViewable(false);
		} else {

			// Expose the file to readers (e.g. via e-commerce).
			$submissionFile->setViewable(true);

			// Log the approve proof event.
			import('lib.pkp.classes.log.SubmissionLog');
			import('classes.log.SubmissionEventLogEntry'); // constants
			$user = $request->getUser();

			$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
			$publicationFormat = $publicationFormatDao->getById($submissionFile->getAssocId(), $submission->getId());

			SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_PROOFS_APPROVED, 'submission.event.proofsApproved', array('formatName' => $publicationFormat->getLocalizedName(),'name' => $user->getFullName(), 'username' => $user->getUsername()));
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->updateObject($submissionFile);

		// update the submission's file index
		import('classes.search.MonographSearchIndex');
		MonographSearchIndex::clearMonographFiles($submission);
		MonographSearchIndex::indexMonographFiles($submission);

		return DAO::getDataChangedEvent($submissionFile->getId());
	}


	//
	// Protected helper methods
	//
	protected function _saveGeneralPromote($args, $request) {
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
		import('lib.pkp.classes.workflow.WorkflowStageDAO');
		$redirectWorkflowStage = WorkflowStageDAO::getIdFromPath($redirectOp);
		$userAccessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);
		if (!array_key_exists($redirectWorkflowStage, $userAccessibleWorkflowStages)) {
			$redirectOp = null;
		}

		return $this->_saveEditorDecision($args, $request, 'PromoteForm', $redirectOp);
	}

	/**
	 * Get editor decision notification type and level by decision.
	 * @param $decision int
	 * @return array
	 */
	protected function _getNotificationTypeByEditorDecision($decision) {
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW;
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				return NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT;
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW;
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				return NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS;
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				return NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT;
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				return NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE;
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				return NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION;
			default:
				assert(false);
				return null;
		}
	}

	/**
	 * Get review-related stage IDs.
	 * @return array
	 */
	protected function _getReviewStages() {
		return array(WORKFLOW_STAGE_ID_INTERNAL_REVIEW, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
	}

	/**
	 * Get review-related decision notifications.
	 */
	protected function _getReviewNotificationTypes() {
		return array(NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS, NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS);
	}

	/**
	 * Get the fully-qualified import name for the given form name.
	 * @param $formName Class name for the desired form.
	 * @return string
	 */
	protected function _resolveEditorDecisionForm($formName) {
		switch($formName) {
			case 'InitiateInternalReviewForm':
			case 'InitiateExternalReviewForm':
				return "controllers.modals.editorDecision.form.$formName";
			default:
				return parent::_resolveEditorDecisionForm($formName);
		}
	}
}

?>
