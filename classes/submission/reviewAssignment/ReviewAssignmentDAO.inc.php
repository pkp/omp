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
import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignmentDAO');

class ReviewAssignmentDAO extends PKPReviewAssignmentDAO {
	var $monographFileDao;
	var $monographCommentsDao;

	/**
	 * Constructor.
	 */
	function ReviewAssignmentDAO() {
		parent::PKPReviewAssignmentDAO();
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Return the review file ID for a submission, given its submission ID.
	 * @param $submissionId int
	 * @return int
	 */
	function _getSubmissionReviewFileId($submissionId) {
		$result =& $this->retrieve(
			'SELECT review_file_id FROM monographs WHERE monograph_id = ?',
			(int) $submissionId
		);
		$returner = isset($result->fields[0]) ? $result->fields[0] : null;
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get all review assignments for a monograph.
	 * @param $monographId int optional
	 * @param $reviewType int optional
	 * @return array ReviewAssignments
	 */
	function &getByMonographId($monographId, $round = null, $reviewType = null) {
		$returner =& $this->getBySubmissionId($monographId, $round, $reviewType);
		return $returner;
	}

	/**
	 * Assign file to be used for review assignments
	 * @param $monographId int The MonographId
	 * @param $reviewType int the review round type
	 * @param $round int the review round number
	 * @param $fileIds array int array of fileId being assigned
	 */
	function setFilesForReview($monographId, $reviewType, $round, $fileIds) {
		// remove the file, in case its there currently in there and replace with the currently selected ones
		$returner = $this->update('DELETE FROM review_round_files
						WHERE monograph_id = ? AND review_type = ? AND round = ?',
						array($monographId, $reviewType, $round));

		// now insert the selected files
		foreach ($fileIds as $fileId) {
			$returner = $returner &&
						$this->update('INSERT INTO review_round_files
								(monograph_id, review_type, round, file_id)
								VALUES (?, ?, ?, ?)',
								array($monographId, $reviewType, $round, $fileId));
		}

		return $returner;
	}

	/**
	 * Get a review file for a monograph for each round.
	 * @param $monographId int
	 * @return array MonographFiles
	 */
	function &getReviewFilesByRound($monographId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT	mf.*, rr.review_type, rr.round as round
			FROM	review_rounds rr,
				monographs m,
				monograph_files mf,
				review_round_files rrf
			WHERE	rr.submission_id = ? AND
				mf.monograph_id = rr.submission_id AND
				m.monograph_id = rr.submission_id AND
				rr.submission_id = mf.monograph_id AND
				mf.revision = rr.review_revision AND
				rrf.review_type = rr.review_type AND
				rrf.round = rr.round AND
				rrf.file_id = mf.file_id',
					(int) $monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']][$row['round']][$row['file_id']] =& $this->monographFileDao->_fromRow($row);
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
			'SELECT	f.*, a.reviewer_id AS reviewer_id, a.review_id
			FROM	review_assignments a,
				monograph_files f
			WHERE	a.reviewer_file_id = f.file_id AND
				f.viewable = 1 AND
				a.submission_id = ?
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
	 * Delete review assignments by monograph.
	 * @param $monographId int
	 * @return boolean
	 */
	function deleteByMonographId($monographId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->deleteBySubmissionId($monographId);
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
			'SELECT	r.reviewer_id, AVG(r.quality) AS average, COUNT(r.quality) AS count
			FROM	review_assignments r, monographs a
			WHERE	r.submission_id = a.monograph_id AND
				a.press_id = ?
			GROUP BY r.reviewer_id',
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
			'SELECT	r.reviewer_id, COUNT(r.review_id) AS count
			FROM	review_assignments r,
				monographs a
			WHERE	r.submission_id = a.monograph_id AND
				a.press_id = ? AND
				r.date_completed IS NOT NULL AND
				r.cancelled = 0
			GROUP BY r.reviewer_id',
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
			WHERE	r.submission_id = a.monograph_id AND
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
			WHERE	r.submission_id = a.monograph_id AND
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
		$reviewAssignment =& parent::_fromRow($row);
		$reviewFileId = $this->_getSubmissionReviewFileId($reviewAssignment->getSubmissionId());
		$reviewAssignment->setReviewFileId($reviewFileId);

		// Files
		$reviewAssignment->setReviewFile($this->monographFileDao->getMonographFile($reviewFileId, $row['review_revision']));
		$reviewAssignment->setReviewerFile($this->monographFileDao->getMonographFile($row['reviewer_file_id']));
		$reviewAssignment->setReviewerFileRevisions($this->monographFileDao->getMonographFileRevisions($row['reviewer_file_id']));

		// Comments
		$reviewAssignment->setMostRecentPeerReviewComment($this->monographCommentDao->getMostRecentMonographComment($row['submission_id'], COMMENT_TYPE_PEER_REVIEW, $row['review_id']));

		HookRegistry::call('ReviewAssignmentDAO::_fromRow', array(&$reviewAssignment, &$row));
		return $reviewAssignment;
	}

	//
	// Add class to temporarily consolidate PKPReviewAssignmentDAO and ReviewAssignmentDAO
	//
	function updateObject(&$reviewAssignment) {
		parent::updateReviewAssignment($reviewAssignment);
	}
}

?>
