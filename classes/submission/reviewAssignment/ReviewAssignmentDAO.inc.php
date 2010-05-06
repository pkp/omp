<?php

/**
 * @file classes/submission/reviewAssignment/ReviewAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignmentDAO
 * @ingroup submission
 * @see ReviewAssignment
 *
 * @brief Class for DAO relating reviewers to monographs.
 */

// $Id$


import('classes.submission.reviewAssignment.ReviewAssignment');

class ReviewAssignmentDAO extends DAO {
	var $userDao;
	var $monographFileDao;
	var $monographCommentsDao;

	/**
	 * Constructor.
	 */
	function ReviewAssignmentDAO() {
		parent::DAO();
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Retrieve a review assignment by reviewer and monograph.
	 * @param $monographId int
	 * @param $reviewerId int
	 * @return ReviewAssignment
	 */
	function &getReviewAssignment($monographId, $reviewerId, $reviewType, $round) {

		$result =& $this->retrieve('
					SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
					FROM review_assignments r 
					LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
					LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
					LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
					WHERE r.monograph_id = ? AND 
						r.reviewer_id = ? AND 
						r.cancelled <> 1 AND 
						r.review_type = ? AND 
						r.round = ?',
					array((int) $monographId, (int) $reviewerId, (int) $reviewType, (int) $round)
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
	 * Retrieve a review assignment by review assignment id.
	 * @param $reviewId int
	 * @return ReviewAssignment
	 */
	function &getById($reviewId) {
		$result =& $this->retrieve(
			'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE r.review_id = ?',
			(int) $reviewId
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
	 * Determine the order of active reviews for the given round of the given monograph
	 * @param $monographId int
	 * @param $reviewType int
	 * @param $round int
	 * @return array associating review ID with number; ie if review ID 26 is first, returned['26']=0
	 */
	function &getReviewIndexesForRound($monographId, $reviewType, $round) {
		$returner = array();
		$index = 0;
		$result =& $this->retrieve(
			'SELECT review_id 
			FROM review_assignments 
			WHERE monograph_id = ? AND 
				review_type = ? AND 
				round = ? AND
				(cancelled = 0 OR cancelled IS NULL) 
			ORDER BY review_id',
			array((int) $monographId, (int) $reviewType, (int) $round)
			);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_id']] = $index++;
			$result->MoveNext();
		}

		$result->Close();
		unset($result);
		return $returner;
	}


	/**
	 * Get all incomplete review assignments for all presses
	 * @param $monographId int
	 * @return array ReviewAssignments
	 */
	function &getIncomplete() {
		$reviewAssignments = array();

		$result =& $this->retrieve(
			'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE (r.cancelled IS NULL OR r.cancelled = 0) AND 
				r.date_notified IS NOT NULL AND 
				r.date_completed IS NULL AND 
				r.declined <> 1 
			ORDER BY r.monograph_id'
		);

		while (!$result->EOF) {
			$reviewAssignments[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $reviewAssignments;
	}

	/**
	 * Get all review assignments for a monograph.
	 * @param $monographId int
	 * @return array ReviewAssignments
	 */
	function &getByMonographId($monographId, $reviewType, $round = null) {
		$reviewAssignments = array();

		$query = 'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round AND r.review_type = r2.review_type) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE r.monograph_id = ?';

		$orderBy = ' ORDER BY review_id';

		$queryParams[] = (int) $monographId;

		if ($round != null) {
			$query .= ' AND r.round = ?';
			$queryParams[] = (int) $round;
		} else {
			$orderBy .= ', round';
		}

		if ($reviewType != null) {
			$query .= ' AND r.review_type = ?';
			$queryParams[] = (int) $reviewType;
		} else {
			$orderBy .= ', review_type';
		}
		
		$query .= $orderBy;

		$result =& $this->retrieve($query, $queryParams);

		while (!$result->EOF) {
			$reviewAssignments[$result->fields['review_id']] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $reviewAssignments;
	}

	/**
	 * Get all review assignments for a reviewer.
	 * @param $userId int
	 * @return array ReviewAssignments
	 */
	function &getByUserId($userId) {
		$reviewAssignments = array();

		$result =& $this->retrieve(
			'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE r.reviewer_id = ? 
			ORDER BY round, review_id',
			(int) $userId
		);

		while (!$result->EOF) {
			$reviewAssignments[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $reviewAssignments;
	}

	/**
	 * Get all review assignments for a review form.
	 * @param $reviewFormId int
	 * @return array ReviewAssignments
	 */
	function &getByReviewFormId($reviewFormId) {
		$reviewAssignments = array();

		$result =& $this->retrieve(
			'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE r.review_form_id = ? 
			ORDER BY round, review_id',
			(int) $reviewFormId
		);

		while (!$result->EOF) {
			$reviewAssignments[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $reviewAssignments;
	}

	/**
	 * Get a review file for a monograph for each round.
	 * @param $monographId int
	 * @return array MonographFiles
	 */
	function &getReviewFilesByRound($monographId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT a.*, r.review_type, r.round as round 
			FROM review_rounds r, monograph_files a, monographs art 
			WHERE art.monograph_id = r.monograph_id AND 
				r.monograph_id = ? AND 
				r.monograph_id = a.monograph_id AND 
				a.file_id = art.review_file_id AND 
				a.revision = r.review_revision AND 
				a.monograph_id = r.monograph_id', 
			(int) $monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']][$row['round']] =& $this->monographFileDao->_fromRow($row);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get all author-viewable reviewer files for a monograph for each round.
	 * @param $monographId int
	 * @return array returned[round][reviewer_index] = array of MonographFiles
	 */
	function &getAuthorViewableFilesByRound($monographId) {
		$files = array();

		$result =& $this->retrieve(
			'SELECT f.*, a.reviewer_id AS reviewer_id, a.review_id
			FROM review_assignments a, monograph_files f 
			WHERE a.reviewer_file_id = f.file_id AND 
				f.viewable = 1 AND 
				a.monograph_id = ?
			ORDER BY a.round, a.reviewer_id, a.review_id', 
			array((int) $monographId)
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($files[$row['round']]) || !is_array($files[$row['round']])) {
				$files[$row['round']] = array();
				$thisReviewerId = $row['reviewer_id'];
				$reviewerIndex = 0;
			} else if ($thisReviewerId != $row['reviewer_id']) {
				$thisReviewerId = $row['reviewer_id'];
				$reviewerIndex++;
			}

			$thisMonographFile =& $this->monographFileDao->_fromRow($row);
			$files[$row['round']][$reviewerIndex][$row['review_id']][] = $thisMonographFile;
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $files;
	}

	/**
	 * Get the most recent last modified date for all review assignments for each round of a submission.
	 * @param $monographId int
	 * @param $round int
	 * @return array associating round with most recent last modified date
	 */
	function &getLastModifiedByRound($monographId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT round, review_type, MAX(last_modified) AS last_modified 
			FROM review_assignments 
			WHERE monograph_id = ? 
			GROUP BY round, review_type', 
			(int) $monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']][$row['round']] = $this->datetimeFromDB($row['last_modified']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the first notified date from all review assignments for a round of a submission.
	 * @param $monographId int
	 * @param $round int
	 * @return array Associative array of ($round_num => $earliest_date_of_notification)*
	 */
	function &getEarliestNotificationByRound($monographId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT round, review_type, MIN(date_notified) AS earliest_date 
			FROM review_assignments 
			WHERE monograph_id = ? 
			GROUP BY round, review_type', 
			(int) $monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']][$row['round']] = $this->datetimeFromDB($row['earliest_date']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get all cancelled/declined review assignments for a monograph.
	 * @param $monographId int
	 * @return array ReviewAssignments
	 */
	function &getCancelsAndRegrets($monographId) {
		$reviewAssignments = array();

		$result =& $this->retrieve(
			'SELECT r.*, r2.review_revision, a.review_file_id, u.first_name, u.last_name 
			FROM review_assignments r 
			LEFT JOIN users u ON (r.reviewer_id = u.user_id) 
			LEFT JOIN review_rounds r2 ON (r.monograph_id = r2.monograph_id AND r.round = r2.round) 
			LEFT JOIN monographs a ON (r.monograph_id = a.monograph_id) 
			WHERE r.monograph_id = ? AND 
				(r.cancelled = 1 OR r.declined = 1) 
			ORDER BY round, review_id',
			(int) $monographId
		);

		while (!$result->EOF) {
			$reviewAssignments[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $reviewAssignments;
	}

	/**
	 * Insert a new Review Assignment.
	 * @param $reviewAssignment ReviewAssignment
	 */	
	function insertObject(&$reviewAssignment) {
		$this->update(
			sprintf('INSERT INTO review_assignments
				(monograph_id, reviewer_id, review_type, round, competing_interests, regret_message recommendation, declined, replaced, cancelled, date_assigned, date_notified, date_confirmed, date_completed, date_acknowledged, date_due, reviewer_file_id, quality, date_rated, last_modified, date_reminded, reminder_was_automatic, step, review_form_id)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, %s, %s, %s, %s, ?, ?, %s, %s, %s, ?, %s, ?)',
				$this->datetimeToDB($reviewAssignment->getDateAssigned()), $this->datetimeToDB($reviewAssignment->getDateNotified()), $this->datetimeToDB($reviewAssignment->getDateConfirmed()), $this->datetimeToDB($reviewAssignment->getDateCompleted()), $this->datetimeToDB($reviewAssignment->getDateAcknowledged()), $this->datetimeToDB($reviewAssignment->getDateDue()), $this->datetimeToDB($reviewAssignment->getDateRated()), $this->datetimeToDB($reviewAssignment->getLastModified()), $this->datetimeToDB($reviewAssignment->getDateReminded()), 1),
			array(
				(int) $reviewAssignment->getMonographId(),
				(int) $reviewAssignment->getReviewerId(),
				(int) $reviewAssignment->getReviewType(),	
				max((int) $reviewAssignment->getRound(), 1),
				$reviewAssignment->getCompetingInterests(),
				$reviewAssignment->getRegretMessage(),
				$reviewAssignment->getRecommendation(),
				(int) $reviewAssignment->getDeclined(),
				(int) $reviewAssignment->getReplaced(),
				(int) $reviewAssignment->getCancelled(),
				$reviewAssignment->getReviewerFileId(),
				$reviewAssignment->getQuality(),
				$reviewAssignment->getReminderWasAutomatic(),
				$reviewAssignment->getReviewFormId(),
			)
		);

		$reviewAssignment->setReviewId($this->getInsertReviewId());
		return $reviewAssignment->getReviewId();
	}

	/**
	 * Update an existing review assignment.
	 * @param $reviewAssignment object
	 */
	function updateObject(&$reviewAssignment) {
		return $this->update(
			sprintf('UPDATE review_assignments
				SET	monograph_id = ?,
					reviewer_id = ?,
					review_type = ?,
					round = ?,
					competing_interests = ?,
					regret_message = ?,
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
					reviewer_file_id = ?,
					quality = ?,
					date_rated = %s,
					last_modified = %s,
					date_reminded = %s,
					reminder_was_automatic = ?,
					review_form_id = ?
				WHERE review_id = ?',
				$this->datetimeToDB($reviewAssignment->getDateAssigned()), $this->datetimeToDB($reviewAssignment->getDateNotified()), $this->datetimeToDB($reviewAssignment->getDateConfirmed()), $this->datetimeToDB($reviewAssignment->getDateCompleted()), $this->datetimeToDB($reviewAssignment->getDateAcknowledged()), $this->datetimeToDB($reviewAssignment->getDateDue()), $this->datetimeToDB($reviewAssignment->getDateRated()), $this->datetimeToDB($reviewAssignment->getLastModified()), $this->datetimeToDB($reviewAssignment->getDateReminded())),
			array(
				(int) $reviewAssignment->getMonographId(),
				(int) $reviewAssignment->getReviewerId(),
				(int) $reviewAssignment->getReviewType(),
				(int) $reviewAssignment->getRound(),
				$reviewAssignment->getCompetingInterests(),
				$reviewAssignment->getRegretMessage(),
				$reviewAssignment->getRecommendation(),
				(int) $reviewAssignment->getDeclined(),
				(int) $reviewAssignment->getReplaced(),
				(int) $reviewAssignment->getCancelled(),
				$reviewAssignment->getReviewerFileId(),
				$reviewAssignment->getQuality(),
				$reviewAssignment->getReminderWasAutomatic(),
				$reviewAssignment->getReviewFormId(),
				(int) $reviewAssignment->getReviewId()
			)
		);
	}

	/**
	 * Delete review assignment.
	 * @param $reviewId int
	 */
	function deleteById($reviewId) {
		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormResponseDao->deleteReviewFormResponseByReviewId($reviewId);

		return $this->update(
			'DELETE FROM review_assignments WHERE review_id = ?',
			(int) $reviewId
		);
	}

	/**
	 * Delete review assignments by monograph.
	 * @param $monographId int
	 * @return boolean
	 */
	function deleteByMonographId($monographId) {
		$returner = false;
		$result =& $this->retrieve(
			'SELECT review_id FROM review_assignments WHERE monograph_id = ?',
			(int) $monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$reviewId = $row['review_id'];

			$this->update('DELETE FROM review_form_responses WHERE review_id = ?', $reviewId);
			$this->update('DELETE FROM review_assignments WHERE review_id = ?', $reviewId);

			$result->MoveNext();
			$returner = true;
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get the ID of the last inserted review assignment.
	 * @return int
	 */
	function getInsertReviewId() {
		return $this->getInsertId('review_assignments', 'review_id');
	}

	/**
	 * Get the average quality ratings and number of ratings for all users of a press.
	 * @return array
	 */
	function getAverageQualityRatings($pressId) {
		$averageQualityRatings = array();
		$result =& $this->retrieve(
			'SELECT R.reviewer_id, AVG(R.quality) AS average, COUNT(R.quality) AS count FROM review_assignments R, monographs A WHERE R.monograph_id = A.monograph_id AND A.press_id = ? GROUP BY R.reviewer_id',
			(int) $pressId
			);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$averageQualityRatings[$row['reviewer_id']] = array('average' => $row['average'], 'count' => $row['count']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $averageQualityRatings;
	}

	/**
	 * Get the average quality ratings and number of ratings for all users of a press.
	 * @return array
	 */
	function getCompletedReviewCounts($pressId) {
		$returner = array();
		$result =& $this->retrieve(
			'SELECT r.reviewer_id, COUNT(r.review_id) AS count FROM review_assignments r, monographs a WHERE r.monograph_id = a.monograph_id AND a.press_id = ? AND r.date_completed IS NOT NULL AND r.cancelled = 0 GROUP BY r.reviewer_id',
			(int) $pressId
			);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['reviewer_id']] = $row['count'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the number of completed reviews for all published review forms of a press.
	 * @return array
	 */
	function getCompletedReviewCountsForReviewForms($pressId) {
		$returner = array();
		$result =& $this->retrieve(
			'SELECT	r.review_form_id, COUNT(r.review_id) AS count
			FROM	review_assignments r,
				monographs a,
				review_forms rf
			WHERE	r.monograph_id = a.monograph_id AND
				a.press_id = ? AND
				r.review_form_id = rf.review_form_id AND
				rf.published = 1 AND
				r.date_completed IS NOT NULL
			GROUP BY r.review_form_id',
			(int) $pressId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_form_id']] = $row['count'];
			$result->MoveNext();
		}
		
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the number of active reviews for all published review forms of a press.
	 * @return array
	 */
	function getActiveReviewCountsForReviewForms($pressId) {
		$returner = array();
		$result =& $this->retrieve(
			'SELECT	r.review_form_id, COUNT(r.review_id) AS count
			FROM	review_assignments r,
				monographs a,
				review_forms rf
			WHERE	r.monograph_id = a.monograph_id AND
				a.press_id = ? AND
				r.review_form_id = rf.review_form_id AND
				rf.published = 1 AND
				r.date_confirmed IS NOT NULL AND
				r.date_completed IS NULL
			GROUP BY r.review_form_id',
			$pressId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_form_id']] = $row['count'];
			$result->MoveNext();
		}
		
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ReviewAssignment
	 */
	function newDataObject() {
		return new ReviewAssignment();
	}

	/**
	 * Internal function to return a review assignment object from a row.
	 * @param $row array
	 * @return ReviewAssignment
	 */
	function &_fromRow(&$row) {
		$reviewAssignment = $this->newDataObject();

		$reviewAssignment->setReviewId($row['review_id']);
		$reviewAssignment->setMonographId($row['monograph_id']);
		$reviewAssignment->setReviewerId($row['reviewer_id']);
		$reviewAssignment->setReviewerFullName($row['first_name'].' '.$row['last_name']);
		$reviewAssignment->setCompetingInterests($row['competing_interests']);
		$reviewAssignment->setRegretMessage($row['regret_message']);
		$reviewAssignment->setRecommendation($row['recommendation']);
		$reviewAssignment->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
		$reviewAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
		$reviewAssignment->setDateConfirmed($this->datetimeFromDB($row['date_confirmed']));
		$reviewAssignment->setDateCompleted($this->datetimeFromDB($row['date_completed']));
		$reviewAssignment->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
		$reviewAssignment->setDateDue($this->datetimeFromDB($row['date_due']));
		$reviewAssignment->setLastModified($this->datetimeFromDB($row['last_modified']));
		$reviewAssignment->setDeclined($row['declined']);
		$reviewAssignment->setReplaced($row['replaced']);
		$reviewAssignment->setCancelled($row['cancelled']);
		$reviewAssignment->setReviewerFileId($row['reviewer_file_id']);
		$reviewAssignment->setQuality($row['quality']);
		$reviewAssignment->setDateRated($this->datetimeFromDB($row['date_rated']));
		$reviewAssignment->setDateReminded($this->datetimeFromDB($row['date_reminded']));
		$reviewAssignment->setReminderWasAutomatic($row['reminder_was_automatic']);
		$reviewAssignment->setRound($row['round']);
		$reviewAssignment->setReviewFileId($row['review_file_id']);
		$reviewAssignment->setReviewRevision($row['review_revision']);
		$reviewAssignment->setReviewFormId($row['review_form_id']);
		$reviewAssignment->setReviewType($row['review_type']);

		// Files
		$reviewAssignment->setReviewFile($this->monographFileDao->getMonographFile($row['review_file_id'], $row['review_revision']));
		$reviewAssignment->setReviewerFile($this->monographFileDao->getMonographFile($row['reviewer_file_id']));
		$reviewAssignment->setReviewerFileRevisions($this->monographFileDao->getMonographFileRevisions($row['reviewer_file_id']));


		// Comments
		$reviewAssignment->setMostRecentPeerReviewComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PEER_REVIEW, $row['review_id']));

		HookRegistry::call('ReviewAssignmentDAO::_fromRow', array(&$reviewAssignment, &$row));

		return $reviewAssignment;
	}

}

?>
