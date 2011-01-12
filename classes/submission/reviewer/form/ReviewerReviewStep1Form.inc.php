<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep1Form.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep1Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 1 of a review.
 */



import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep1Form extends ReviewerReviewForm {
	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 */
	function ReviewerReviewStep1Form($reviewerSubmission = null) {
		parent::ReviewerReviewForm($reviewerSubmission, 1);

		$press =& Request::getPress();
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$user =& Request::getUser();
		$submission = $this->reviewerSubmission;

		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($submission->getReviewId());

		if ($submission->getDateConfirmed() == null) {
			$confirmedStatus = 0;
		} else {
			$confirmedStatus = 1;
		}

		$templateMgr =& TemplateManager::getManager();

		$reviewerRequestParams = array('reviewer' => $reviewAssignment->getReviewerFullName(),
										'personalNote' => 'EDITOR NOTE',
										'editor' => $press->getSetting('contactName'));
		$templateMgr->assign('reviewerRequest', Locale::translate('reviewer.step1.requestBoilerplate', $reviewerRequestParams));

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);
		$templateMgr->assign_by_ref('press', $press);
		$templateMgr->assign_by_ref('reviewGuidelines', $press->getLocalizedSetting('reviewGuidelines'));
		$templateMgr->assign('step', 1);
		$templateMgr->assign('completedSteps', $submission->getStatus());
		$templateMgr->assign('blindReview', true); // FIXME: Need to be able to get/set if a review is blind or not

		// FIXME: Need press setting that denotes competing interests are required
		$templateMgr->assign('competingInterestsText', $submission->getCompetingInterests());


		import('classes.submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

		$templateMgr->assign('helpTopicId', 'editorial.reviewersRole.review');

		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('competingInterestOption', 'competingInterestText'));
	}

	function getTemplateFile() {
		return 'reviewer/review/step1.tpl';
	}

	/**
	 * Save changes to submission.
	 */
	function execute() {
		// Set review to next step
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		if($this->reviewerSubmission->getStep() < 2) {
			$this->reviewerSubmission->setStep(2);
		}

		if ($this->getData('competingInterestOption') == 'hasCompetingInterests') {
				$this->reviewerSubmission->setCompetingInterests(Request::getUserVar('competingInterestsText'));
		} else {
			$this->reviewerSubmission->setCompetingInterests(null);
		}
		$reviewerSubmissionDao->updateReviewerSubmission($this->reviewerSubmission);

		// Set that the reviewer has accepted the review
		ReviewerAction::confirmReview($this->reviewerSubmission, false, true);
	}

}

?>
