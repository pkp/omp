<?php

/**
 * @file pages/authorDashboard/AuthorDashboardHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardHandler
 * @ingroup pages_authorDashboard
 *
 * @brief Handle requests for the author dashboard.
 */

// Import base class
import('classes.handler.Handler');

class AuthorDashboardHandler extends Handler {

	/**
	 * Constructor
	 */
	function AuthorDashboardHandler() {
		parent::Handler();
		$this->addRoleAssignment($this->_getAssignmentRoles(), array('submission', 'readSubmissionEmail'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpAuthorDashboardAccessPolicy');
		$this->addPolicy(new OmpAuthorDashboardAccessPolicy($request, $args, $roleAssignments), true);

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler operations
	//
	/**
	 * Displays the author dashboard.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submission($args, $request) {
		// Pass the authorized submission on to the template.
		$this->setupTemplate($request);
		$templateMgr = TemplateManager::getManager($request);
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$templateMgr->assign('submission', $submission);

		// "View metadata" action.
		import('controllers.modals.submissionMetadata.linkAction.AuthorViewMetadataLinkAction');
		$viewMetadataAction = new AuthorViewMetadataLinkAction($request, $submission->getId());
		$templateMgr->assign('viewMetadataAction', $viewMetadataAction);

		// Import submission file to define file stages.
		import('lib.pkp.classes.submission.SubmissionFile');

		// Workflow-stage specific "upload file" action.
		$fileStage = null;
		$currentStage = $submission->getStageId();
		switch ($currentStage) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				$fileStage = SUBMISSION_FILE_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$fileStage = SUBMISSION_FILE_REVIEW_REVISION;
				break;

			case WORKFLOW_STAGE_ID_EDITING:
				$fileStage = SUBMISSION_FILE_FINAL;
				break;
		}

		$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
		$internalReviewRounds = $reviewRoundDao->getBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$externalReviewRounds = $reviewRoundDao->getBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
		$lastInternalReviewRound = $reviewRoundDao->getLastReviewRoundBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$lastExternalReviewRound = $reviewRoundDao->getLastReviewRoundBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
		$lastInternalReviewRoundNumber = 0;
		$lastExternalReviewRoundNumber = 0;
		if ($lastInternalReviewRound) {
			$lastInternalReviewRoundNumber = $lastInternalReviewRound->getRound();
		}
		if ($lastExternalReviewRound) {
			$lastExternalReviewRoundNumber = $lastExternalReviewRound->getRound();
		}
		$lastReviewRoundNumber = array('internalReview' => $lastInternalReviewRoundNumber,
			'externalReview' => $lastExternalReviewRoundNumber);
		$templateMgr->assign_by_ref('internalReviewRounds', $internalReviewRounds);
		$templateMgr->assign_by_ref('externalReviewRounds', $externalReviewRounds);
		$templateMgr->assign('lastReviewRoundNumber', $lastReviewRoundNumber);

		// Get the last review round.
		$lastReviewRound =& $reviewRoundDao->getLastReviewRoundBySubmissionId($submission->getId(), $currentStage);

		// Create and assign add file link action.
		if ($fileStage && is_a($lastReviewRound, 'ReviewRound')) {
			import('lib.pkp.controllers.api.file.linkAction.AddFileLinkAction');
			$uploadFileAction = new AddFileLinkAction(
				$request, $submission->getId(), $currentStage,
				array(ROLE_ID_AUTHOR), $fileStage, null, null, $lastReviewRound->getId());
			$templateMgr->assign('uploadFileAction', $uploadFileAction);
		}


		// If the submission is in or past the editorial stage,
		// assign the editor's copyediting emails to the template
		$submissionEmailLogDao = DAORegistry::getDAO('SubmissionEmailLogDAO');
		$user = $request->getUser();

		if ($submission->getStageId() >= WORKFLOW_STAGE_ID_EDITING) {
			$copyeditingEmails = $submissionEmailLogDao->getByEventType($submission->getId(), SUBMISSION_EMAIL_COPYEDIT_NOTIFY_AUTHOR, $user->getId());
			$templateMgr->assign_by_ref('copyeditingEmails', $copyeditingEmails);
		}

		// Same for production stage.
		if ($submission->getStageId() == WORKFLOW_STAGE_ID_PRODUCTION) {
			$productionEmails = $submissionEmailLogDao->getByEventType($submission->getId(), SUBMISSION_EMAIL_PROOFREAD_NOTIFY_AUTHOR, $user->getId());
			$templateMgr->assign_by_ref('productionEmails', $productionEmails);
		}

		// Define the notification options.
		$submissionAssocTypeAndIdArray = array(ASSOC_TYPE_SUBMISSION, $submission->getId());
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_TASK => array(
				NOTIFICATION_TYPE_SIGNOFF_COPYEDIT => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_SIGNOFF_PROOF => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS => $submissionAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE => $submissionAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION => $submissionAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);
		$templateMgr->assign('authorDashboardNotificationRequestOptions', $notificationRequestOptions);

		$templateMgr->display('authorDashboard/authorDashboard.tpl');
	}


	/**
	 * Fetches information about a specific email and returns it.
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function readSubmissionEmail($args, $request) {
		$submissionEmailLogDao = DAORegistry::getDAO('SubmissionEmailLogDAO');
		$user = $request->getUser();
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$submissionEmailId = $request->getUserVar('submissionEmailId');

		$submissionEmailFactory = $submissionEmailLogDao->getByEventType($submission->getId(), SUBMISSION_EMAIL_EDITOR_NOTIFY_AUTHOR, $user->getId());
		while ($email = $submissionEmailFactory->next()) { // validate the email id for this user.
			if ($email->getId() == $submissionEmailId) {
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->assign('submissionEmail', $email);
				return $templateMgr->fetchJson('authorDashboard/submissionEmail.tpl');
			}
		}
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_APP_EDITOR, LOCALE_COMPONENT_PKP_GRID);
	}

	/**
	 * Get roles to assign to operations in this handler.
	 * @return array
	 */
	function _getAssignmentRoles() {
		return array(ROLE_ID_AUTHOR);
	}
}

?>
