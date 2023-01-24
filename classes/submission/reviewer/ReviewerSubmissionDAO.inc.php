<?php

/**
 * @file classes/submission/reviewer/ReviewerSubmissionDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionDAO
 * @ingroup submission
 * @see ReviewerSubmission
 *
 * @brief Operations for retrieving and modifying ReviewerSubmission objects.
 */

import('classes.submission.SubmissionDAO');
import('classes.submission.reviewer.ReviewerSubmission');

class ReviewerSubmissionDAO extends SubmissionDAO {
	var $authorDao;
	var $userDao;
	var $reviewAssignmentDao;
	var $submissionCommentDao;

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->authorDao = DAORegistry::getDAO('AuthorDAO');
		$this->userDao = DAORegistry::getDAO('UserDAO');
		$this->reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->submissionCommentDao = DAORegistry::getDAO('SubmissionCommentDAO');
	}

	/**
	 * Retrieve a reviewer submission by monograph ID.
	 * @param $monographId int
	 * @param $reviewerId int
	 * @return ReviewerSubmission|null
	 */
	function getReviewerSubmission($reviewId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		// Each column of review_assignments table is taken separately intentionaly so that
		// same named columns like stage_id from different tables is treated separately in _fromRow function
		$result = $this->retrieve(
			'SELECT	m.*, p.date_published,
				r.review_id,
				r.submission_id,
				r.reviewer_id,
				r.competing_interests,
				r.recommendation,
				r.date_assigned,
				r.date_notified,
				r.date_confirmed,
				r.date_completed,
				r.date_acknowledged,
				r.date_due,
				r.date_response_due,
				r.last_modified as last_modified_ra,
				r.reminder_was_automatic,
				r.declined,
				r.cancelled,
				r.reviewer_file_id,
				r.date_rated,
				r.date_reminded,
				r.quality,
				r.review_round_id,
				r.stage_id as stage_id_ra,
				r.review_method,
				r.round,
				r.step,
				r.review_form_id,
				r.unconsidered,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title
			FROM	submissions m
				LEFT JOIN publications p ON (m.current_publication_id = p.publication_id)
				LEFT JOIN review_assignments r ON (m.submission_id = r.submission_id)
				LEFT JOIN series s ON (s.series_id = p.series_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
			WHERE	r.review_id = ?',
			[
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				(int) $reviewId
			]
		);
		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ReviewerSubmission
	 */
	function newDataObject() {
		return new ReviewerSubmission();
	}

	/**
	 * Internal function to return a ReviewerSubmission object from a row.
	 * @param $row array
	 * @return ReviewerSubmission
	 */
	function _fromRow($row) {
		// Get the ReviewerSubmission object, populated with Monograph data
		$reviewerSubmission = parent::_fromRow($row);
		$reviewer = $this->userDao->getById($row['reviewer_id']);

		// Editor Decisions
		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO'); /* @var $editDecisionDao EditDecisionDAO */
		$decisions = $editDecisionDao->getEditorDecisions($row['submission_id']);
		$reviewerSubmission->setDecisions($decisions);

		// Review Assignment
		$reviewerSubmission->setReviewId($row['review_id']);
		$reviewerSubmission->setReviewerId($row['reviewer_id']);
		$reviewerSubmission->setReviewerFullName($reviewer->getFullName());
		$reviewerSubmission->setCompetingInterests($row['competing_interests']);
		$reviewerSubmission->setRecommendation($row['recommendation']);
		$reviewerSubmission->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
		$reviewerSubmission->setDateNotified($this->datetimeFromDB($row['date_notified']));
		$reviewerSubmission->setDateConfirmed($this->datetimeFromDB($row['date_confirmed']));
		$reviewerSubmission->setDateCompleted($this->datetimeFromDB($row['date_completed']));
		$reviewerSubmission->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
		$reviewerSubmission->setDateDue($this->datetimeFromDB($row['date_due']));
		$reviewerSubmission->setDateResponseDue($this->datetimeFromDB($row['date_response_due']));
		$reviewerSubmission->setDeclined($row['declined']);
		$reviewerSubmission->setCancelled($row['cancelled']);
		$reviewerSubmission->setQuality($row['quality']);
		$reviewerSubmission->setRound($row['round']);
		$reviewerSubmission->setStep($row['step']);
		$reviewerSubmission->setStageId($row['stage_id']);
		$reviewerSubmission->setReviewAssignmentStageId($row['stage_id_ra']);
		$reviewerSubmission->setReviewMethod($row['review_method']);

		HookRegistry::call('ReviewerSubmissionDAO::_fromRow', array(&$reviewerSubmission, &$row));
		return $reviewerSubmission;
	}

	/**
	 * Update an existing review submission.
	 * @param $reviewSubmission ReviewSubmission
	 */
	function updateReviewerSubmission($reviewerSubmission) {
		$this->update(
			sprintf('UPDATE review_assignments
				SET	submission_id = ?,
					reviewer_id = ?,
					stage_id = ?,
					review_method = ?,
					round = ?,
					step = ?,
					competing_interests = ?,
					recommendation = ?,
					declined = ?,
					cancelled = ?,
					date_assigned = %s,
					date_notified = %s,
					date_confirmed = %s,
					date_completed = %s,
					date_acknowledged = %s,
					date_due = %s,
					date_response_due = %s,
					quality = ?
				WHERE	review_id = ?',
				$this->datetimeToDB($reviewerSubmission->getDateAssigned()),
				$this->datetimeToDB($reviewerSubmission->getDateNotified()),
				$this->datetimeToDB($reviewerSubmission->getDateConfirmed()),
				$this->datetimeToDB($reviewerSubmission->getDateCompleted()),
				$this->datetimeToDB($reviewerSubmission->getDateAcknowledged()),
				$this->datetimeToDB($reviewerSubmission->getDateDue()),
				$this->datetimeToDB($reviewerSubmission->getDateResponseDue())),
			[
				(int) $reviewerSubmission->getId(),
				(int) $reviewerSubmission->getReviewerId(),
				(int) $reviewerSubmission->getReviewAssignmentStageId(),
				(int) $reviewerSubmission->getReviewMethod(),
				(int) $reviewerSubmission->getRound(),
				(int) $reviewerSubmission->getStep(),
				$reviewerSubmission->getCompetingInterests(),
				(int) $reviewerSubmission->getRecommendation(),
				(int) $reviewerSubmission->getDeclined(),
				(int) $reviewerSubmission->getCancelled(),
				(int) $reviewerSubmission->getQuality(),
				(int) $reviewerSubmission->getReviewId()
			]
		);
	}
}


