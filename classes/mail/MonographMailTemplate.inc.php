<?php

/**
 * @file classes/mail/MonographMailTemplate.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographMailTemplate
 * @ingroup mail
 *
 * @brief Subclass of MailTemplate for sending emails related to submissions.
 *
 * This allows for submission-specific functionality like logging, etc.
 */

import('lib.pkp.classes.mail.SubmissionMailTemplate');
import('classes.log.SubmissionEmailLogEntry'); // Bring in log constants

class MonographMailTemplate extends SubmissionMailTemplate {
	/**
	 * Constructor.
	 * @param $submission Submission
	 * @param $emailKey string optional
	 * @param $locale string optional
	 * @param $enableAttachments boolean optional
	 * @param $context object optional
	 * @param $includeSignature boolean optional
	 * @see MailTemplate::MailTemplate()
	 */
	function MonographMailTemplate($submission, $emailKey = null, $locale = null, $enableAttachments = null, $context = null, $includeSignature = true) {
		parent::SubmissionMailTemplate($submission, $emailKey, $locale, $enableAttachments, $context, $includeSignature);
	}

	function assignParams($paramArray = array()) {
		$submission = $this->submission;
		$paramArray['seriesName'] = strip_tags($submission->getSeriesTitle());
		parent::assignParams($paramArray);
	}

	/**
	 *  Send this email to all assigned series editors in the given stage
	 * @param $submissionId int
	 * @param $stageId int
	 */
	function toAssignedSeriesEditors($submissionId, $stageId) {
		return $this->toAssignedSubEditors($submissionId, $stageId);
	}

	/**
	 * CC this email to all assigned series editors in the given stage
	 * @param $submissionId int
	 * @param $stageId int
	 * @return array of Users (note, this differs from OxS which returns EditAssignment objects)
	 */
	function ccAssignedSeriesEditors($submissionId, $stageId) {
		return $this->ccAssignedSubEditors($submissionId, $stageId);
	}

	/**
	 * BCC this email to all assigned series editors in the given stage
	 * @param $submissionId int
	 * @param $stageId int
	 */
	function bccAssignedSeriesEditors($submissionId, $stageId) {
		return $this->bccAssignedSubEditors($submissionId, $stageId);
	}
}

?>
