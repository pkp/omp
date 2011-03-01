<?php

/**
 * @file ReviewRoundDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundDAO
 * @ingroup monograph_reviewRound
 * @see ReviewRound
 *
 * @brief Operations for retrieving and modifying ReviewRound objects.
 */


import('classes.monograph.reviewRound.ReviewRound');

class ReviewRoundDAO extends DAO {

	/**
	 * Constructor
	 */
	function ReviewRoundDAO() {
		parent::DAO();
	}


	//
	// Public methods
	//
	/**
	 * Fetch a review round, creating it if needed.
	 * @param $submissionId integer
	 * @param $reviewType integer One of the REVIEW_TYPE_* constants.
	 * @param $round integer
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 * @return ReviewRound
	 */
	function build($submissionId, $reviewType, $round, $status = null) {
		// If one exists, fetch and return.
		$reviewRound = $this->getReviewRound($submissionId, $reviewType, $round);
		if ($reviewRound) return $reviewRound;

		// Otherwise, build one.
		unset($reviewRound);
		$reviewRound =& $this->newDataObject();
		$reviewRound->setSubmissionId($submissionId);
		$reviewRound->setRound($round);
		$reviewRound->setReviewType($reviewType);
		$reviewRound->setStatus($status);
		$this->insertObject($reviewRound);
		return $reviewRound;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new ReviewRound();
	}

	/**
	 * Insert a new review round.
	 * @param $reviewRound ReviewRound
	 * @return int
	 */
	function insertObject(&$reviewRound) {
		$this->update(
				'INSERT INTO review_rounds
				(submission_id, review_type, round, status)
				VALUES
				(?, ?, ?, ?)',
				array(
					$reviewRound->getSubmissionId(),
					$reviewRound->getReviewType(),
					$reviewRound->getRound(),
					$reviewRound->getStatus()
				)
		);
		return $reviewRound;
	}

	/**
	 * Update an existing review round.
	 * @param $reviewRound ReviewRound
	 * @return boolean
	 */
	function updateObject(&$reviewRound) {
		$returner = $this->update(
			'UPDATE	review_rounds
			SET	status = ?
			WHERE	submission_id = ? AND
				review_type = ? AND
				round = ?',
			array(
				$reviewRound->getStatus(),
				$reviewRound->getSubmissionId(),
				$reviewRound->getReviewType(),
				$reviewRound->getRound()
			)
		);
		return $returner;
	}

	/**
	 * Retrieve a review round
	 * @param $symbolic string
	 * @param $assocType int
	 * @param $assocId int
	 */
	function getReviewRound($submissionId, $reviewType, $round) {
		$result =& $this->retrieve(
			'SELECT * FROM review_rounds WHERE submission_id = ? AND review_type = ? AND round = ?',
			array($submissionId, (int) $reviewType, (int) $round)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Check if a review round exists for a specified monograph.
	 * @param $monographIdId int
	 * @param $round int
	 * @return boolean
	 */
	function reviewRoundExists($monographId, $reviewType, $round) {
		$result = &$this->retrieve(
				'SELECT COUNT(*) FROM review_rounds WHERE submission_id = ? AND review_type = ? AND round = ?',
				array((int)$monographId, (int)$reviewType, (int)$round));
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}


	//
	// Private methods
	//
	/**
	 * Internal function to return a review round object from a row.
	 * @param $row array
	 * @return Signoff
	 */
	function _fromRow(&$row) {
		$reviewRound = $this->newDataObject();

		$reviewRound->setSubmissionId((int)$row['submission_id']);
		$reviewRound->setReviewType((int)$row['review_type']);
		$reviewRound->setRound((int)$row['round']);
		$reviewRound->setStatus((int)$row['status']);

		return $reviewRound;
	}
}

?>
