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
	}

	/**
	 * Display the submission review page.
	 * @param $args array
	 */
	function submission($args, &$request) {
		$press =& Request::getPress();
		$reviewId = $args[0];

		$this->validate($request, $reviewId);
		$user =& $this->user;
		$submission =& $this->submission;
		$this->setupTemplate(true, $submission->getId(), $reviewId);

		$reviewStep = $submission->getStep(); // Get the current saved step from the DB
		$userStep = $request->getUserVar('step');
		$step = isset($userStep) ? $userStep: $reviewStep;
		if($step > $reviewStep) $step = $reviewStep; // Reviewer can't go past incomplete steps

		if($step < 4) {
			$formClass = "ReviewerReviewStep{$step}Form";
			import("classes.submission.reviewer.form.$formClass");

			$reviewerForm = new $formClass($submission);

			if ($reviewerForm->isLocaleResubmit()) {
				$reviewerForm->readInputData();
			} else {
				$reviewerForm->initData();
			}
			$reviewerForm->display();
		} else {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('submission', $submission);
			$templateMgr->assign('step', 4);
			$templateMgr->display('reviewer/review/reviewCompleted.tpl');
		}
	}

	/**
	 * Save a review step.
	 * @param $args array first parameter is the step being saved
	 */
	function saveStep($args, &$request) {
		$step = isset($args[0]) ? $args[0] : 1;
		$reviewId = $request->getUserVar('reviewId');

		$this->validate($request, $reviewId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $submission->getId(), $reviewId);

		$formClass = "ReviewerReviewStep{$step}Form";
		import("classes.submission.reviewer.form.$formClass");

		$reviewerForm = new $formClass($submission);
		$reviewerForm->readInputData();

		if ($reviewerForm->validate()) {
			$reviewerForm->execute();

			$request->redirect(null, null, 'submission', $reviewId, array('step' => $step+1));
		} else {
			$reviewerForm->display();
		}
	}

	/**
	 * Show a form for the reviewer to enter regrets into.
	 * @param $args array optional
	 */
	function showDeclineReview($args, &$request) {
		$reviewId = Request::getUserVar('reviewId');

		$this->validate($request, $reviewId);
		$reviewerSubmission =& $this->submission;

		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $this->submission);

		$json = new JSON('true', $templateMgr->fetch('reviewer/review/regretMessage.tpl'));
		echo $json->getString();
	}

	/**
	 * Save the reviewer regrets form and decline the review.
	 * @param $args array optional
	 */
	function saveDeclineReview($args, &$request) {
		$reviewId = Request::getUserVar('reviewId');
		$declineReviewMessage = Request::getUserVar('declineReviewMessage');

		$this->validate($request, $reviewId);
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
	 */
	function downloadFile($args, &$request) {
		$reviewId = isset($args[0]) ? $args[0] : 0;
		$monographId = isset($args[1]) ? $args[1] : 0;
		$fileId = isset($args[2]) ? $args[2] : 0;
		$revision = isset($args[3]) ? $args[3] : null;

		$this->validate($request, $reviewId);
		$reviewerSubmission =& $this->submission;

		if (!ReviewerAction::downloadReviewerFile($reviewId, $reviewerSubmission, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $reviewId);
		}
	}


	//
	// Validation
	//
	/**
	 * Validate that the user is an assigned reviewer for
	 * the monograph.
	 * Redirects to reviewer index page if validation fails.
	 */
	function validate(&$request, $reviewId) {
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$press =& $request->getPress();
		$user =& $request->getUser();

		$isValid = true;
		$newKey = Request::getUserVar('key');

		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);

		if (!$reviewerSubmission || $reviewerSubmission->getPressId() != $press->getId()) {
			$isValid = false;
		} elseif ($user) {
			if ($reviewerSubmission->getReviewerId() != $user->getId()) {
				$isValid = false;
			}
		} else {
			$user =& SubmissionReviewHandler::validateAccessKey($reviewerSubmission->getReviewerId(), $reviewId, $newKey);
			if (!$user) $isValid = false;
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		$this->submission =& $reviewerSubmission;
		$this->user =& $user;
		return true;
	}
}
?>
