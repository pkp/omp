<?php

/**
 * @file classes/submission/reviewer/ReviewerSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionDAO
 * @ingroup submission
 * @see ReviewerSubmission
 *
 * @brief Operations for retrieving and modifying ReviewerSubmission objects.
 */

// $Id$


import('classes.submission.reviewer.ReviewerSubmission');

class ReviewerSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $reviewAssignmentDao;
	var $monographFileDao;
	var $monographCommentDao;

	/**
	 * Constructor.
	 */
	function ReviewerSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Retrieve a reviewer submission by monograph ID.
	 * @param $monographId int
	 * @param $reviewerId int
	 * @return ReviewerSubmission
	 */
	function &getReviewerSubmission($reviewId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT	a.*,
				r.*,
				r2.review_revision,
				u.first_name, u.last_name,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs a
				LEFT JOIN review_assignments r ON (a.monograph_id = r.submission_id)
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN users u ON (r.reviewer_id = u.user_id)
				LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.review_type = r2.review_type AND r.round = r2.round)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	r.review_id = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$reviewId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new ReviewerSubmission();
	}

	/**
	 * Internal function to return a ReviewerSubmission object from a row.
	 * @param $row array
	 * @return ReviewerSubmission
	 */
	function &_fromRow(&$row) {
		$reviewerSubmission = $this->newDataObject();

		// Files
		$reviewerSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));
		$reviewerSubmission->setRevisedFile($this->monographFileDao->getMonographFile($row['revised_file_id']));
		$reviewerSubmission->setReviewFile($this->monographFileDao->getMonographFile($row['review_file_id']));
		$reviewerSubmission->setReviewerFile($this->monographFileDao->getMonographFile($row['reviewer_file_id']));
		$reviewerSubmission->setReviewerFileRevisions($this->monographFileDao->getMonographFileRevisions($row['reviewer_file_id']));

		// Comments
		$reviewerSubmission->setMostRecentPeerReviewComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PEER_REVIEW, $row['review_id']));

		// Editor Decisions
		$decisions =& $this->getEditorDecisions($row['monograph_id']);
		$reviewerSubmission->setDecisions($decisions);

		// Review Assignment
		$reviewerSubmission->setReviewId($row['review_id']);
		$reviewerSubmission->setReviewerId($row['reviewer_id']);
		$reviewerSubmission->setReviewerFullName($row['first_name'].' '.$row['last_name']);
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
		$reviewerSubmission->setReplaced($row['replaced']);
		$reviewerSubmission->setCancelled($row['cancelled']==1?1:0);
		$reviewerSubmission->setReviewerFileId($row['reviewer_file_id']);
		$reviewerSubmission->setQuality($row['quality']);
		$reviewerSubmission->setRound($row['round']);
		$reviewerSubmission->setStep($row['step']);
		$reviewerSubmission->setReviewType($row['review_type']);
		$reviewerSubmission->setReviewFileId($row['review_file_id']);
		$reviewerSubmission->setReviewRevision($row['review_revision']);

		// Monograph attributes
		$this->monographDao->_monographFromRow($reviewerSubmission, $row);

		HookRegistry::call('ReviewerSubmissionDAO::_fromRow', array(&$reviewerSubmission, &$row));
		return $reviewerSubmission;
	}

	/**
	 * Update an existing review submission.
	 * @param $reviewSubmission ReviewSubmission
	 */
	function updateReviewerSubmission(&$reviewerSubmission) {
		$returner =& $this->update(
			sprintf('UPDATE review_assignments
				SET	submission_id = ?,
					reviewer_id = ?,
					review_type = ?,
					review_method = ?,
					round = ?,
					step = ?,
					competing_interests = ?,
					recommendation = ?,
					declined = ?,
					replaced = ?,
					cancelled = ?,
					date_assigned = %s,
					date_notified = %s,
					date_confirmed = %s,
					date_completed = %s,
					date_acknowledged = %s,
					date_due = %s,
					date_response_due = %s,
					reviewer_file_id = ?,
					quality = ?
				WHERE review_id = ?',
				$this->datetimeToDB($reviewerSubmission->getDateAssigned()), $this->datetimeToDB($reviewerSubmission->getDateNotified()), $this->datetimeToDB($reviewerSubmission->getDateConfirmed()), $this->datetimeToDB($reviewerSubmission->getDateCompleted()), $this->datetimeToDB($reviewerSubmission->getDateAcknowledged()), $this->datetimeToDB($reviewerSubmission->getDateDue()), $this->datetimeToDB($reviewerSubmission->getDateResponseDue())),
			array(
				$reviewerSubmission->getId(),
				$reviewerSubmission->getReviewerId(),
				$reviewerSubmission->getReviewType(),
				$reviewerSubmission->getReviewMethod(),
				$reviewerSubmission->getRound(),
				$reviewerSubmission->getStep(),
				$reviewerSubmission->getCompetingInterests(),
				$reviewerSubmission->getRecommendation(),
				$reviewerSubmission->getDeclined(),
				$reviewerSubmission->getReplaced(),
				$reviewerSubmission->getCancelled(),
				$reviewerSubmission->getReviewerFileId(),
				$reviewerSubmission->getQuality(),
				$reviewerSubmission->getReviewId()
			)
		);
		return $returner;
	}

	/**
	 * Get all submissions for a reviewer of a press.
	 * @param $reviewerId int
	 * @param $pressId int
	 * @param $rangeInfo object
	 * @return array ReviewerSubmissions
	 */
	function &getReviewerSubmissionsByReviewerId($reviewerId, $pressId = null, $active = true, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$sql = 'SELECT	a.*,
				r.*,
				r2.review_revision,
				u.first_name, u.last_name,
				atl.setting_value AS submission_title,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs a
				LEFT JOIN review_assignments r ON (a.monograph_id = r.submission_id)
				LEFT JOIN monograph_settings atl ON (atl.monograph_id = a.monograph_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN users u ON (r.reviewer_id = u.user_id)
				LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.review_type = r2.review_type AND r.round = r2.round)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE r.reviewer_id = ?' . ($pressId?	' AND a.press_id = ? ':'') .
				'AND r.date_notified IS NOT NULL';

		if ($active) {
			$sql .=  ' AND r.date_completed IS NULL AND r.declined <> 1 AND (r.cancelled = 0 OR r.cancelled IS NULL)';
		} else {
			$sql .= ' AND (r.date_completed IS NOT NULL OR r.cancelled = 1 OR r.declined = 1)';
		}

		if ($sortBy) {
			$sql .=  ' ORDER BY ' . $sortBy . ' ' . $this->getDirectionMapping($sortDirection);
		}

		$params = array(
					'title',
					$locale,
					'title',
					$primaryLocale,
					'title',
					$locale,
					'abbrev',
					$primaryLocale,
					'abbrev',
					$locale,
					$reviewerId
				);
		if($pressId) $params[] = $pressId;

		$result =& $this->retrieveRange($sql, $params, $rangeInfo);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get count of active and complete assignments
	 * @param reviewerId int
	 * @param pressId int
	 */
	function getSubmissionsCount($reviewerId, $pressId) {
		$submissionsCount = array();
		$submissionsCount[0] = 0;
		$submissionsCount[1] = 0;

		$sql = 'SELECT	r.date_completed, r.declined, r.cancelled
			FROM	monographs a
				LEFT JOIN review_assignments r ON (a.monograph_id = r.submission_id)
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN users u ON (r.reviewer_id = u.user_id)
				LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.review_type = r2.review_type AND r.round = r2.round)
			WHERE	a.press_id = ? AND
				r.reviewer_id = ? AND
				r.date_notified IS NOT NULL';

		$result =& $this->retrieve($sql, array($pressId, $reviewerId));

		while (!$result->EOF) {
			if ($result->fields['date_completed'] == null && $result->fields['declined'] != 1 && $result->fields['cancelled'] != 1) {
				$submissionsCount[0] += 1;
			} else {
				$submissionsCount[1] += 1;
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $submissionsCount;
	}

	/**
	 * Get the editor decisions for a review round of a monograph.
	 * @param $monographId int
	 * @param $round int
	 */
	function getEditorDecisions($monographId, $round = null) {
		$decisions = array();

		if ($round == null) {
			$result =& $this->retrieve(
				'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE monograph_id = ? ORDER BY date_decided ASC', $monographId
			);
		} else {
			$result =& $this->retrieve(
				'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE monograph_id = ? AND round = ? ORDER BY date_decided ASC',
				array($monographId, $round)
			);
		}

		while (!$result->EOF) {
			$decisions[] = array(
				'editDecisionId' => $result->fields['edit_decision_id'],
				'editorId' => $result->fields['editor_id'],
				'decision' => $result->fields['decision'],
				'dateDecided' => $this->datetimeFromDB($result->fields['date_decided'])
			);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $decisions;
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'id': return 'a.monograph_id';
			case 'assignDate': return 'r.date_assigned';
			case 'dueDate': return 'r.date_due';
			case 'section': return 'section_abbrev';
			case 'title': return 'submission_title';
			case 'round': return 'r.round';
			case 'review': return 'r.recommendation';
			case 'decision': return 'editor_decision';
			default: return null;
		}
	}
}

?>
