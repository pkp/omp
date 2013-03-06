<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep2Form.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep2Form
 * @ingroup submission_form
 *
 * @brief Form for Step 2 of author manuscript submission.
 */


import('classes.submission.form.SubmissionSubmitForm');

class SubmissionSubmitStep2Form extends SubmissionSubmitForm {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep2Form($context, $submission) {
		parent::SubmissionSubmitForm($context, $submission, 2);
	}

	/**
	 * Save changes to submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the submission ID
	 */
	function execute($args, &$request) {
		// Update submission
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submission = $this->submission;

		if ($submission->getSubmissionProgress() <= $this->step) {
			$submission->stampStatusModified();
			$submission->setSubmissionProgress($this->step + 1);
			$submissionDao->updateObject($submission);
		}

		return $this->submissionId;
	}
}

?>
