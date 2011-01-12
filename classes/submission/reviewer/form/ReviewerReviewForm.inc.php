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

	/** @var Submission current submission */
	var $reviewerSubmission;

	/** @var int the current step */
	var $step;

	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 * @param $step integer
	 */
	function ReviewerReviewForm($reviewerSubmission, $step) {
		parent::Form(sprintf('reviewer/review/step%d.tpl', $step));
		$this->addCheck(new FormValidatorPost($this));
		$this->step = (int) $step;
		$this->reviewerSubmission = $reviewerSubmission;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		parent::display();
	}
}

?>