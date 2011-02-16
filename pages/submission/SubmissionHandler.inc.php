<?php

/**
 * @file SubmissionHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for monograph submission functions.
 */

// Import base class
import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.linkAction.request.WizardModal');

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
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
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
		import('lib.pkp.classes.linkAction.LegacyLinkAction');
		$router =& $request->getRouter();
		$dispatcher =& $this->getDispatcher();
		$actionArgs = array('monographId' => $monograph->getId());
		// FIXME: grid actions should not be referred to from outside a grid. This breaks the
		// encapsulation rules for widgets, see #6411.
		$uploadFileAction = new LinkAction(
			'addFile',
			new WizardModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.submission.AuthorSubmissionDetailsFilesGridHandler', 'addFile', null, $actionArgs),
				'submission.submit.uploadSubmissionFile',
				'fileManagement'
			),
			'submission.addFile',
			'add_item'
		);

		$uploadRevisionAction = new LinkAction(
			'addRevision',
			new WizardModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.submission.AuthorSubmissionDetailsFilesGridHandler', 'addFile', null, array_merge(array('revisionOnly' => true), $actionArgs)),
				'submission.submit.uploadRevision',
				'fileManagement'
			),
			'submission.uploadARevision',
			'edit'
		);

		$addCopyeditedFileAction = new LinkAction(
			'addCopyeditedFile',
			new WizardModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.files.copyedit.AuthorCopyeditingFilesGridHandler', 'addCopyeditedFile', null, $actionArgs),
				'submission.uploadACopyeditedVersion',
				'fileManagement'
			),
			'submission.uploadACopyeditedVersion',
			'add_item'
		);
		$viewMetadataAction = new LinkAction(
			'viewMetadata',
			new AjaxModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler', 'fetch', null, $actionArgs),
				'submission.viewMetadata'
			),
			'submission.viewMetadata',
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
		$json = new JSON(true, $templateMgr->fetch('submission/reviewRoundInfo.tpl'));
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
