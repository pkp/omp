<?php

/**
 * @file SubmissionReviewHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionReviewHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission tracking.
 */


import('pages.reviewer.ReviewerHandler');
import('lib.pkp.classes.core.JSON');

class SubmissionReviewHandler extends ReviewerHandler {
	/** submission associated with the request **/
	var $submission;

	/** user associated with the request **/
	var $user;

	/**
	 * Constructor
	 */
	function SubmissionReviewHandler() {
		parent::ReviewerHandler();

		$this->addRoleAssignment(ROLE_ID_REVIEWER, array('submission', 'saveStep', 'showDeclineReview', 'saveDeclineReview', 'downloadFile'));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display the submission review page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submission($args, &$request) {
		$reviewId = $request->getUserVar('reviewId');

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		$this->setupTemplate();

		$reviewStep = $reviewerSubmission->getStep(); // Get the current saved step from the DB
		$userStep = $request->getUserVar('step');
		$step = isset($userStep) ? $userStep: $reviewStep;
		if($step > $reviewStep) $step = $reviewStep; // Reviewer can't go past incomplete steps

		if($step < 4) {
			$formClass = "ReviewerReviewStep{$step}Form";
			import("classes.submission.reviewer.form.$formClass");

			$reviewerForm = new $formClass($reviewerSubmission);

			if ($reviewerForm->isLocaleResubmit()) {
				$reviewerForm->readInputData();
			} else {
				$reviewerForm->initData();
			}
			$reviewerForm->display();
		} else {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('submission', $reviewerSubmission);
			$templateMgr->assign('step', 4);
			$templateMgr->display('reviewer/review/reviewCompleted.tpl');
		}
	}

	/**
	 * Save a review step.
	 * @param $args array first parameter is the step being saved
	 * @param $request PKPRequest
	 */
	function saveStep($args, &$request) {
		$step = isset($args[0]) ? $args[0] : 1;
		$reviewId = $request->getUserVar('reviewId');

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		$this->setupTemplate();

		$formClass = "ReviewerReviewStep{$step}Form";
		import("classes.submission.reviewer.form.$formClass");

		$reviewerForm = new $formClass($reviewerSubmission);
		$reviewerForm->readInputData();

		if ($reviewerForm->validate()) {
			$reviewerForm->execute();

			$request->redirect(null, null, 'submission', $step+1, array('monographId' => $reviewerSubmission->getId(), 'reviewId' => $reviewId));
		} else {
			$reviewerForm->display();
		}
	}

	/**
	 * Show a form for the reviewer to enter regrets into.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function showDeclineReview($args, &$request) {
		$reviewId = $request->getUserVar('reviewId');

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		$this->setupTemplate();


		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $reviewerSubmission);

		$json = new JSON('true', $templateMgr->fetch('reviewer/review/regretMessage.tpl'));
		echo $json->getString();
	}

	/**
	 * Save the reviewer regrets form and decline the review.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveDeclineReview($args, &$request) {
		$reviewId = Request::getUserVar('reviewId');
		$declineReviewMessage = Request::getUserVar('declineReviewMessage');

		$reviewerSubmission =& $this->submission;

		// Save regret message
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($reviewId);
		$reviewAssignment->setRegretMessage($declineReviewMessage);
		$reviewAssignmentDao->updateObject($reviewAssignment);

		ReviewerAction::confirmReview($reviewerSubmission, true, true);
		$request->redirect($request->redirect(null, 'reviewer'));
	}


	//
	// Misc
	//
	/**
	 * Download a file.
	 * @param $args array ($monographId, $fileId, [$revision])
	 * @param $request PKPRequest
	 */
	function downloadFile($args, &$request) {
		$reviewId = isset($args[0]) ? $args[0] : 0;
		$monographId = isset($args[1]) ? $args[1] : 0;
		$fileId = isset($args[2]) ? $args[2] : 0;
		$revision = isset($args[3]) ? $args[3] : null;

		$reviewerSubmission =& $this->submission;

		if (!ReviewerAction::downloadReviewerFile($reviewId, $reviewerSubmission, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $reviewId);
		}
	}
}
?>
