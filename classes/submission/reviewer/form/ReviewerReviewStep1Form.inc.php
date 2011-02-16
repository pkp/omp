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
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::display()
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		// Add submission parameters.
		$submission =& $this->getReviewerSubmission();
		$templateMgr->assign('completedSteps', $submission->getStatus());
		// FIXME: Need press setting that denotes competing interests are required, see #6402.
		$templateMgr->assign('competingInterestsText', $submission->getCompetingInterests());

		// Add review assignment.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($submission->getReviewId());
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);

		// Add press parameters.
		$press =& Request::getPress();
		$templateMgr->assign_by_ref('press', $press);
		// FIXME: Need to be able to get/set if a review is blind or not, see #6403.
		$templateMgr->assign('blindReview', true);

		// Add reviewer request text.
		$reviewerRequestParams = array('reviewer' => $reviewAssignment->getReviewerFullName(),
										'personalNote' => 'EDITOR NOTE',
										'editor' => $press->getSetting('contactName'));
		$templateMgr->assign('reviewerRequest', Locale::translate('reviewer.step1.requestBoilerplate', $reviewerRequestParams));

		parent::display();
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('competingInterestOption', 'competingInterestText'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute() {
		$reviewerSubmission =& $this->getReviewerSubmission();

		// Set competing interests.
		if ($this->getData('competingInterestOption') == 'hasCompetingInterests') {
				$reviewerSubmission->setCompetingInterests(Request::getUserVar('competingInterestsText'));
		} else {
			$reviewerSubmission->setCompetingInterests(null);
		}

		// Set review to next step.
		$this->updateReviewStepAndSaveSubmission($reviewerSubmission);

		// Set that the reviewer has accepted the review.
		ReviewerAction::confirmReview($reviewerSubmission, false, true);
	}

}

?>
