<?php

/**
 * @file classes/submission/reviewAssignment/ReviewAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignmentDAO
 * @ingroup submission
 * @see ReviewAssignment
 *
 * @brief Class for DAO relating reviewers to monographs.
 */



import('classes.submission.reviewAssignment.ReviewAssignment');
import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignmentDAO');

class ReviewAssignmentDAO extends PKPReviewAssignmentDAO {
	var $submissionFileDao;
	var $monographCommentsDao;

	/**
	 * Constructor.
	 */
	function ReviewAssignmentDAO() {
		parent::PKPReviewAssignmentDAO();
		$this->submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
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
