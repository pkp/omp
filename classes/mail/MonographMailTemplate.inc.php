<?php

/**
 * @file classes/mail/MonographMailTemplate.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
		$paramArray['seriesName'] = strip_tags($submission->getSeriesTitle());
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($submission->getSeriesId());
		$paramArray['seriesPath'] = $series ? $series->getPath() : '';		
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


