<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep2Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep2Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 2 of a review.
 */



import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep2Form extends ReviewerReviewForm {
	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 */
	function ReviewerReviewStep2Form($reviewerSubmission = null) {
		parent::ReviewerReviewForm($reviewerSubmission, 2);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$press = Request::getPress();

		$reviewerGuidelines = $press->getLocalizedSetting('reviewGuidelines');
		if (empty($reviewerGuidelines)) {
			$reviewerGuidelines = Locale::translate('reviewer.monograph.noGuidelines');
		}
		$templateMgr->assign_by_ref('reviewerGuidelines', $press->getLocalizedSetting('reviewGuidelines'));
		$templateMgr->assign_by_ref('submission', $this->reviewerSubmission);
		$templateMgr->assign('step', 2);

		parent::display();
	}


	/**
	 * Save changes to submission.
	 */
	function execute() {
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		if($this->reviewerSubmission->getStep() < 3) {
			$this->reviewerSubmission->setStep(3);
		}
		$reviewerSubmissionDao->updateReviewerSubmission($this->reviewerSubmission);
	}

}

?>
