<?php

/**
 * @file SubmissionHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for monograph submission functions.
 */


import('classes.handler.Handler');

class SubmissionHandler extends Handler {
	/**
	 * Constructor
	 */
	function SubmissionHandler() {
		parent::Handler();
		$this->addRoleAssignment(array(ROLE_ID_REVIEWER), $reviewerOperations = array('index'));
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR), $authorOperations = array_merge($reviewerOperations, array('authorDetails', 'reviewRoundInfo')));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		switch($operation) {
			case 'index':
				// The user only needs press-level permission to see a list
				// of submissions.
				import('classes.security.authorization.OmpPressAccessPolicy');
				$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
				break;
			default:
				// All other operations require full submission access.
				import('classes.security.authorization.OmpSubmissionAccessPolicy');
				$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		}
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Displays the details of a single submission.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function authorDetails($args, &$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();

		// Pass the authorized monograph on to the template.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Setup author actions
		import('lib.pkp.classes.linkAction.LinkAction');
		$router =& $request->getRouter();
		$dispatcher =& $this->getDispatcher();
		$actionArgs = array('monographId' => $monograph->getId(), 'stageId' => $monograph->getCurrentStageId());
		$uploadFileAction = new LinkAction(
				'addFile',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.submissionFiles.SubmissionDetailsFilesGridHandler', 'addFile', null, array_merge($actionArgs, array('gridId' => 'submissiondetailsfilesgrid'))),
				'submission.addFileToBook',
				null,
				'add_item'
			);
		import('classes.monograph.MonographFile');
		$actionArgs['fileStage'] = MONOGRAPH_FILE_REVIEW;
		$uploadRevisionAction = new LinkAction(
				'addRevision',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_NOTHING,
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.submissionFiles.SubmissionDetailsFilesGridHandler', 'addRevision', null, array_merge($actionArgs, array('gridId' => 'submissiondetailsfilesgrid'))),
				'submission.uploadARevision',
				null,
				'edit'
			);
		$actionArgs['fileStage'] = MONOGRAPH_FILE_COPYEDIT;
		$addCopyeditedFileAction = new LinkAction(
				'addCopyeditedFile',
				LINK_ACTION_MODE_MODAL,
				null,
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.authorCopyeditingFiles.AuthorCopyeditingFilesGridHandler', 'addCopyeditedFile', null, array_merge($actionArgs, array('gridId' => 'authorcopyeditingfilesgrid'))),
				'submission.uploadACopyeditedVersion',
				null,
				'add_item'
			);
		$viewMetadataAction = new LinkAction(
				'viewMetadata',
				LINK_ACTION_MODE_MODAL,
				null,
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler', 'fetch', null, $actionArgs),
				'submission.viewMetadata',
				null,
				'more_info'
			);

		// If we are at or past the review stage, pass review round info on to the template
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

		// Set allRounds to an array of all values > 0 and less than currentRound--This will determine the tabs to show
		$allRounds = array();
		for ($i = 1; $i <= $monograph->getCurrentRound(); $i++) $allRounds[] = $i;
		$templateMgr->assign('rounds', $allRounds);

		// If the submission is in or past the copyediting stage, assign the editor's copyediting emails to the template
		if ($monograph->getCurrentStageId() > 3) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$monographEmails =& $monographEmailLogDao->getMonographLogEntriesByAssoc($monograph->getId(), MONOGRAPH_EMAIL_TYPE_COPYEDIT, MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR);

			$templateMgr->assign_by_ref('monographEmails', $monographEmails);
			$templateMgr->assign('showCopyeditingFiles', true);
		}


		$templateMgr->assign('uploadFileAction', $uploadFileAction);
		$templateMgr->assign('uploadRevisionAction', $uploadRevisionAction);
		$templateMgr->assign('addCopyeditedFileAction', $addCopyeditedFileAction);
		$templateMgr->assign('viewMetadataAction', $viewMetadataAction);

 		$templateMgr->display('submission/authorDetails.tpl');
	}

	/**
	 * Fetch information for the author on the specified review round
	 * @param $args array
	 * @param $request Request
	 */
	function reviewRoundInfo($args, &$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();

		$round = (int) $request->getUserVar('round');
		$templateMgr->assign('round', $round);

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->getReviewRound($monograph->getId(), 1, $round);

		// Get the status message for the round
		$roundStatus =& $reviewRound->getStatusKey();
		$templateMgr->assign('roundStatus', $roundStatus);

		// Editor has taken an action and sent an email; Display the email
		if($reviewRound->getStatus() != REVIEW_ROUND_STATUS_PENDING_REVIEWERS && $reviewRound->getStatus() != REVIEW_ROUND_STATUS_PENDING_REVIEWS) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$monographEmails =& $monographEmailLogDao->getMonographLogEntriesByAssoc($monograph->getId(), MONOGRAPH_EMAIL_TYPE_EDITOR, $round);

			$templateMgr->assign_by_ref('monographEmails', $monographEmails);
			$templateMgr->assign('showReviewAttachments', true);
		}

		import('lib.pkp.classes.core.JSON');
		$json = new JSON('true', $templateMgr->fetch('submission/reviewRoundInfo.tpl'));
		return $json->getString();
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
