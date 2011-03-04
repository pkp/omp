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
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR), array('index', 'reviewRoundInfo'));
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
	function index($args, &$request) {
		// Pass the authorized monograph on to the template.
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Setup author actions.
		// FIXME: Grid actions should not be referred to from outside a grid. This breaks the
		// encapsulation rules for widgets, see #6411.
		import('lib.pkp.classes.linkAction.LinkAction');
		import('classes.monograph.MonographFile');
		$dispatcher =& $this->getDispatcher();
		$actionArgs = array('monographId' => $monograph->getId());

		// View metadata action.
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$viewMetadataAction = new LinkAction(
				'viewMetadata',
				new AjaxModal(
						$dispatcher->url($request, ROUTE_COMPONENT, null,
								'modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler',
								'fetch', null, $actionArgs),
						__('submission.viewMetadata')
				),
				__('submission.viewMetadata'),
				'more_info');
		$templateMgr->assign('viewMetadataAction', $viewMetadataAction);

		// Add file action.
		// FIXME: Use pre-configured link actions like AddFileLinkAction.
		import('lib.pkp.classes.linkAction.request.WizardModal');
		$actionArgs['fileStage'] = MONOGRAPH_FILE_SUBMISSION;
		$uploadFileAction = new LinkAction(
				'addFile',
				new WizardModal(
						$dispatcher->url($request, ROUTE_COMPONENT, null,
								'wizard.fileUpload.FileUploadWizardHandler', 'startWizard',
								null, $actionArgs),
						__('submission.submit.uploadSubmissionFile'),
						'fileManagement'),
				__('submission.addFile'),
				'add_item');
		$templateMgr->assign('uploadFileAction', $uploadFileAction);

		// Workflow-stage specific "add file" action.
		switch ($monograph->getCurrentStageId()) {
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$actionArgs['revisionOnly'] = true;
				$actionArgs['fileStage'] = MONOGRAPH_FILE_SUBMISSION;
				$addRevisionAction = new LinkAction(
						'addRevision',
						new WizardModal(
								$dispatcher->url($request, ROUTE_COMPONENT, null,
										'wizard.fileUpload.FileUploadWizardHandler', 'startWizard',
										null, $actionArgs),
								__('submission.submit.uploadRevision'),
								'fileManagement'),
						__('submission.uploadARevision'),
						'edit');
				break;

			// FIXME: Use standard file uploader for copyediting files.
			case WORKFLOW_STAGE_ID_EDITING:
				$actionArgs['fileStage'] = MONOGRAPH_FILE_FINAL;
				$addRevisionAction = new LinkAction(
						'addCopyeditedFile',
						new WizardModal(
								$dispatcher->url($request, ROUTE_COMPONENT, null,
										'grid.files.copyedit.AuthorCopyeditingFilesGridHandler',
										'addCopyeditedFile', null, $actionArgs),
								__('submission.uploadACopyeditedVersion'),
								'fileManagement'),
						__('submission.uploadACopyeditedVersion'),
						'add_item');
				break;

			default:
				$addRevisionAction = null;
		}
		$templateMgr->assign('addRevisionAction', $addRevisionAction);


		// Create an array with one entry per review round.
		// This will determine the tabs to show.
		$reviewRounds = array();
		for ($i = 1; $i <= $monograph->getCurrentRound(); $i++) $reviewRounds[] = $i;
		$templateMgr->assign('rounds', $reviewRounds);


		// If the submission is in or past the copyediting stage,
		// assign the editor's copyediting emails to the template
		if ($monograph->getCurrentStageId() >= WORKFLOW_STAGE_ID_EDITING) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$monographEmails =& $monographEmailLogDao->getMonographLogEntriesByAssoc($monograph->getId(), MONOGRAPH_EMAIL_TYPE_COPYEDIT, MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR);

			$templateMgr->assign_by_ref('monographEmails', $monographEmails);
			$templateMgr->assign('showCopyeditingFiles', true);
		}

 		$templateMgr->display('authorDashboard/index.tpl');
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
		$json = new JSON(true, $templateMgr->fetch('authorDashboard/reviewRoundInfo.tpl'));
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
