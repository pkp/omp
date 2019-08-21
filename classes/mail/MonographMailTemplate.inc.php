<?php

/**
 * @file classes/mail/MonographMailTemplate.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
import('lib.pkp.classes.log.SubmissionEmailLogEntry'); // Bring in log constants

class MonographMailTemplate extends SubmissionMailTemplate {
	/**
	 * Assign parameters to the mail template.
	 * @param $paramArray array
	 */
	function assignParams($paramArray = array()) {
		$submission = $this->submission;
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($submission->getSeriesId());
		$paramArray['seriesPath'] = $series ? $series->getPath() : '';
		$paramArray['seriesName'] = $series ? $series->getLocalizedTitle() : '';
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


