<?php

/**
 * @file SubmissionReviewHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	/**
	 * Constructor
	 */
	function SubmissionReviewHandler() {
		parent::ReviewerHandler();
		$this->addRoleAssignment(ROLE_ID_REVIEWER,
				array('submission', 'saveStep', 'showDeclineReview', 'saveDeclineReview', 'downloadFile'));
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT); /* @var $reviewAssignment ReviewAssignment */
		$reviewId = (int) $reviewAssignment->getId();
		assert(!empty($reviewId));

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO'); /* @var $reviewerSubmissionDao ReviewerSubmissionDAO */
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewAssignment->getId());
		assert(is_a($reviewerSubmission, 'ReviewerSubmission'));

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));
		$this->setupTemplate();

		$reviewStep = max($reviewerSubmission->getStep(), 1); // Get the current saved step from the DB
		$userStep = (int) $request->getUserVar('step');
		$step = (int) (!empty($userStep) ? $userStep: $reviewStep);
		if($step > $reviewStep) $step = $reviewStep; // Reviewer can't go past incomplete steps
		if ($step<1 || $step>4) fatalError('Invalid step!');

		if($step < 4) {
			$formClass = "ReviewerReviewStep{$step}Form";
			import("classes.submission.reviewer.form.$formClass");

			$reviewerForm = new $formClass($reviewerSubmission, $reviewAssignment);

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
		$step = (int)$request->getUserVar('step');
		if ($step<1 || $step>3) fatalError('Invalid step!');

		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT); /* @var $reviewAssignment ReviewAssignment */
		$reviewId = (int) $reviewAssignment->getId();
		assert(!empty($reviewId));

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		assert(is_a($reviewerSubmission, 'ReviewerSubmission'));

		$formClass = "ReviewerReviewStep{$step}Form";
		import("classes.submission.reviewer.form.$formClass");

		$reviewerForm = new $formClass($reviewerSubmission, $reviewAssignment);
		$reviewerForm->readInputData();

		if ($reviewerForm->validate()) {
			$reviewerForm->execute();
			$request->redirect(null, null, 'submission', $reviewAssignment->getMonographId());
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT); /* @var $reviewAssignment ReviewAssignment */
		$reviewId = (int) $reviewAssignment->getId();
		assert(!empty($reviewId));

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		assert(is_a($reviewerSubmission, 'ReviewerSubmission'));

		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $reviewerSubmission);

		$json = new JSON(true, $templateMgr->fetch('reviewer/review/regretMessage.tpl'));
		echo $json->getString();
	}

	/**
	 * Save the reviewer regrets form and decline the review.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveDeclineReview($args, &$request) {
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT); /* @var $reviewAssignment ReviewAssignment */
		$reviewId = (int) $reviewAssignment->getId();
		assert(!empty($reviewId));
		$declineReviewMessage = $request->getUserVar('declineReviewMessage');

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$reviewerSubmission =& $reviewerSubmissionDao->getReviewerSubmission($reviewId);
		assert(is_a($reviewerSubmission, 'ReviewerSubmission'));

		// Save regret message
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($reviewId);
		assert(is_a($reviewAssignment, 'ReviewAssignment'));
		$reviewAssignment->setRegretMessage($declineReviewMessage);
		$reviewAssignmentDao->updateObject($reviewAssignment);

		ReviewerAction::confirmReview($reviewerSubmission, true, true);
		$router =& $request->getRouter(); /* @var $router PageRouter */
		$request->redirect($router->redirectHome($request));
	}


	//
	// Private helper methods
	//
	function _retrieveStep() {
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT); /* @var $reviewAssignment ReviewAssignment */
		$reviewId = (int) $reviewAssignment->getId();
		assert(!empty($reviewId));
		return $reviewId;
	}
}
?>
