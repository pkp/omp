<?php

/**
 * @file ReviewRoundDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundDAO
 * @ingroup monograph_reviewRound
 * @see ReviewRound
 *
 * @brief Operations for retrieving and modifying ReviewRound objects.
 */

//$Id$

import('classes.monograph.reviewRound.ReviewRound');

class ReviewRoundDAO extends DAO {

	/**
	 * Fetch a signoff by symbolic info, building it if needed.
	 * @param $symbolic string
	 * @param $assocType int
	 * @param $assocId int
	 * @return $signoff
	 */
	function build($submissionId, $reviewType, $round, $reviewRevision = null, $status = null) {
		// If one exists, fetch and return.
		$reviewRound = $this->getReviewRound($submissionId, $reviewType, $round);
		if ($reviewRound) return $reviewRound;

		// Otherwise, build one.
		unset($reviewRound);
		$reviewRound = $this->newDataObject();
		$reviewRound->setSubmissionId($submissionId);
		$reviewRound->setRound($round);
		$reviewRound->setReviewType($reviewType);
		$reviewRound->setReviewRevision($reviewRevision);
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
	 * Internal function to return an Signoff object from a row.
	 * @param $row array
	 * @return Signoff
	 */
	function _fromRow(&$row) {
		$reviewRound = $this->newDataObject();

		$reviewRound->setSubmissionId($row['submission_id']);
		$reviewRound->setReviewType($row['review_type']);
		$reviewRound->setRound($row['round']);
		$reviewRound->setReviewRevision($row['review_revision']);
		$reviewRound->setStatus($row['status']);

		return $reviewRound;
	}

	/**
	 * Insert a new review round.
	 * @param $reviewRound ReviewRound
	 * @return int
	 */
	function insertObject(&$reviewRound) {
		$this->update(
				'INSERT INTO review_rounds
				(submission_id, review_type, round, review_revision, status)
				VALUES
				(?, ?, ?, ?)',
				array(
					$reviewRound->getSubmissionId(),
					$reviewRound->getReviewType(),
					$reviewRound->getRound(),
					$reviewRound->getReviewRevision(),
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
			SET	review_revision = ?,
				status = ?
			WHERE	submission_id = ? AND
				review_type = ? AND
				round = ?',
			array(
				$reviewRound->getReviewRevision(),
				$reviewRound->getStatus(),
				$reviewRound->getSubmissionId(),
				$reviewRound->getReviewType(),
				$reviewRound->getRound()
			)
		);
		return $returner;
	}

	/**
	 * Retrieve an array of signoffs matching the specified
	 * symbolic name and assoc info.
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
			'SELECT COUNT(*) FROM review_rounds WHERE submission_id = ? AND review_type = ? AND round = ?', array($monographId, $reviewType, $round)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Initiate a review round
	 * @param int $monographId
	 * @param int $round
	 * @param int $reviewRevision
	 */
	function createReviewRound($monographId, $reviewType, $round, $reviewRevision, $status = null) {
		$this->update(
			'INSERT INTO review_rounds
				(submission_id, review_type, round, review_revision, status)
				VALUES
				(?, ?, ?, ?, ?)',
			array($monographId, $reviewType, $round, $reviewRevision, $status)
		);
	}
}

?>
