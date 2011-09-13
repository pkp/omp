<?php

/**
 * @file pages/authorDashboard/AuthorDashboardHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR), array('submission', 'reviewRoundInfo'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
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
	function submission($args, &$request) {
		// Pass the authorized monograph on to the template.
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// "View metadata" action.
		import('controllers.modals.submissionMetadata.linkAction.AuthorViewMetadataLinkAction');
		$viewMetadataAction = new AuthorViewMetadataLinkAction($request, $monograph->getId());
		$templateMgr->assign('viewMetadataAction', $viewMetadataAction);

		// Import monograph file to define file stages.
		import('classes.monograph.MonographFile');

		// Workflow-stage specific "upload file" action.
		$fileStage = null;
		$currentStage = $monograph->getStageId();
		switch ($currentStage) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				$fileStage = MONOGRAPH_FILE_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$fileStage = MONOGRAPH_FILE_REVIEW_REVISION;
				break;

			case WORKFLOW_STAGE_ID_EDITING:
				$fileStage = MONOGRAPH_FILE_FINAL;
				break;
		}

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$internalReviewRounds =& $reviewRoundDao->getByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$externalReviewRounds =& $reviewRoundDao->getByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
		$templateMgr->assign_by_ref('internalReviewRounds', $internalReviewRounds);
		$templateMgr->assign_by_ref('externalReviewRounds', $externalReviewRounds);

		if ($fileStage) {
			import('controllers.api.file.linkAction.AddFileLinkAction');
			$uploadFileAction = new AddFileLinkAction(
				$request, $monograph->getId(), $currentStage,
				array(ROLE_ID_AUTHOR), $fileStage, null, null, $monograph->getCurrentRound());
			$templateMgr->assign('uploadFileAction', $uploadFileAction);
		}


		// If the submission is in or past the copyediting stage,
		// assign the editor's copyediting emails to the template
		if ($monograph->getStageId() >= WORKFLOW_STAGE_ID_EDITING) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$monographEmails =& $monographEmailLogDao->getByEventType($monograph->getId(), MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR);

			$templateMgr->assign_by_ref('monographEmails', $monographEmails);
		}

		// Define the notification options.
		$monographAssocTypeAndIdArray = array(ASSOC_TYPE_MONOGRAPH, $monograph->getId());
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_TASK => array(
				NOTIFICATION_TYPE_SIGNOFF_COPYEDIT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_SIGNOFF_PROOF => $monographAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION => $monographAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);
		$templateMgr->assign('authorDashboardNotificationRequestOptions', $notificationRequestOptions);

		$templateMgr->display('authorDashboard/authorDashboard.tpl');
	}

	/**
	 * Fetch information for the author on the specified review round
	 * @param $args array
	 * @param $request Request
	 */
	function reviewRoundInfo($args, &$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();

		$stageId = (int)$request->getUserVar('stageId');
		if ($stageId !== WORKFLOW_STAGE_ID_INTERNAL_REVIEW && $stageId !== WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			fatalError('Invalid Stage Id');
		}
		$templateMgr->assign('stageId', $stageId);

		$round = (int) $request->getUserVar('round');
		$templateMgr->assign('round', $round);

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->getReviewRound($monograph->getId(), $stageId, $round);
		assert(isset($reviewRound));

		// Get the status message for the round
		$roundStatus =& $reviewRound->getStatusKey();
		$templateMgr->assign('roundStatus', $roundStatus);

		// Editor has taken an action and sent an email; Display the email
		if($reviewRound->getStatus() != REVIEW_ROUND_STATUS_PENDING_REVIEWERS && $reviewRound->getStatus() != REVIEW_ROUND_STATUS_PENDING_REVIEWS) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$monographEmails =& $monographEmailLogDao->getByEventType($monograph->getId(), MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR);

			$templateMgr->assign_by_ref('monographEmails', $monographEmails);
			$templateMgr->assign('showReviewAttachments', true);
		}

		return $templateMgr->fetch('authorDashboard/reviewRoundInfo.tpl');
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR));
	}
}

?>
