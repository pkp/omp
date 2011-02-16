<?php

/**
 * @defgroup author_form_submit
 */

/**
 * @file classes/submission/reviewer/form/ReviewerReviewForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewForm
 * @ingroup submission_reviewer_form
 *
 * @brief Base class for reviewer forms.
 */



import('lib.pkp.classes.form.Form');

class ReviewerReviewForm extends Form {

	/** @var ReviewerSubmission current submission */
	var $_reviewerSubmission;

	/** @var int the current step */
	var $_step;

	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 * @param $step integer
	 */
	function ReviewerReviewForm($reviewerSubmission, $step) {
		parent::Form(sprintf('reviewer/review/step%d.tpl', $step));
		$this->addCheck(new FormValidatorPost($this));
		$this->_step = (int) $step;
		$this->_reviewerSubmission =& $reviewerSubmission;
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the reviewer submission.
	 * @return ReviewerSubmission
	 */
	function &getReviewerSubmission() {
		return $this->_reviewerSubmission;
	}

	/**
	 * Get the review step.
	 * @return integer
	 */
	function getStep() {
		return $this->_step;
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::display()
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $this->getReviewerSubmission());
		$templateMgr->assign('step', $this->getStep());
		parent::display();
	}


	//
	// Protected helper methods
	//
	/**
	 * Set the review step of the submission to the given
	 * value if it is not already set to a higher value. Then
	 * update the given reviewer submission.
	 * @param $reviewerSubmission ReviewerSubmission
	 */
	function updateReviewStepAndSaveSubmission(&$reviewerSubmission) {
		// Update the review step.
		$nextStep = $this->getStep() + 1;
		if($reviewerSubmission->getStep() < $nextStep) {
			$reviewerSubmission->setStep($nextStep);
		}

		// Save the reviewer submission.
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO'); /* @var $reviewerSubmissionDao ReviewerSubmissionDAO */
		$reviewerSubmissionDao->updateReviewerSubmission($reviewerSubmission);
	}
}

?>