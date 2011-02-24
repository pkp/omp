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
	/** @var SubmissionFileDAO */
	var $_submissionFileDao;

	/**
	 * Constructor
	 */
	function ReviewRoundDAO() {
		parent::DAO();
		$this->_submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
	}

	/**
	 * Fetch a review round, creating it if needed.
	 * @param $submissionId integer
	 * @param $reviewType integer One of the REVIEW_TYPE_* constants.
	 * @param $round integer
	 * @param $reviewRevision integer
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 * @return ReviewRound
	 */
	function build($submissionId, $reviewType, $round, $reviewRevision = null, $status = null) {
		// If one exists, fetch and return.
		$reviewRound = $this->getReviewRound($submissionId, $reviewType, $round);
		if ($reviewRound) return $reviewRound;

		// Otherwise, build one.
		unset($reviewRound);
		$reviewRound =& $this->newDataObject();
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
	 * Internal function to return a review round object from a row.
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
				(?, ?, ?, ?, ?)',
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
			'SELECT COUNT(*) FROM review_rounds WHERE submission_id = ? AND review_type = ? AND round = ?', array($monographId, $reviewType, $round)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Assign file to be used for review assignments
	 * @param $monographId int The MonographId
	 * @param $reviewType int the review round type
	 * @param $round int the review round number
	 * @param $fileIds 2D array of (fileId, revision) being assigned
	 */
	function setFilesForReview($monographId, $reviewType, $round, $fileIds) {
		// Remove currently assigned review files.
		$returner = $this->update('DELETE FROM review_round_files
				WHERE monograph_id = ? AND review_type = ? AND round = ?',
				array((int)$monographId, (int)$reviewType, (int)$round));

		// Insert the updated review files.
		foreach ($fileIds as $fileId) {
			if (!isset($fileId[1])) $fileId[1] = 1; // If no revision is set, default to 1
			$returner = $returner &&
					$this->update('INSERT INTO review_round_files
							(monograph_id, review_type, round, file_id, revision)
							VALUES (?, ?, ?, ?, ?)',
							array((int)$monographId, (int)$reviewType, (int)$round, (int)$fileId[0], (int)$fileId[1]));
		}

		return $returner;
	}

	/**
	 * Get a review file for a monograph for each round.
	 * FIXME: Move to SubmissionFileDAO
	 * @param $monographId int
	 * @return array A three-dimensional array with the review type,
	 *  the round and the corresponding MonographFiles. Returns an
	 *  empty array if now files were found.
	 */
	function &getReviewFilesByRound($monographId) {
		$returner = array();

		$result =& $this->retrieve(
			'SELECT
				mf.file_id AS monograph_file_id, mf.revision as monograph_revision,
				mf.*, af.*,
				rr.review_type, rr.round as round
			FROM review_rounds rr
				INNER JOIN review_round_files rrf
					ON rr.review_type = rrf.review_type AND rr.round = rrf.round
				INNER JOIN monograph_files mf
					ON rr.submission_id = mf.monograph_id AND rrf.file_id = mf.file_id
				LEFT JOIN monograph_artwork_files af ON mf.file_id = af.file_id AND mf.revision = af.revision
			WHERE rr.submission_id = ?',
			(int)$monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$idAndRevision = implode("-", array($row['monograph_file_id'], isset($row['monograph_revision']) ? $row['monograph_revision'] : 1));
			$returner[$row['review_type']][$row['round']][$idAndRevision] =& $this->_submissionFileDao->fromRow($row);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get a list of (file_ids, revisions)  for each round.
	 * @param $monographId int
	 * @return array MonographFiles
	 */
	function &getReviewFilesAndRevisionsByRound($monographId, $round, $concatenate = false) {
		$result =& $this->retrieve(
			'SELECT	mf.file_id, mf.revision
			FROM	review_rounds rr,
				monograph_files mf,
				review_round_files rrf
			WHERE	rr.submission_id = ? AND
				mf.monograph_id = rr.submission_id AND
				rrf.review_type = rr.review_type AND
				rrf.round = rr.round AND
				rrf.file_id = mf.file_id AND
				rrf.revision = mf.revision',
					(int) $monographId
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if($concatenate) $returner[] = $row['file_id'] . "-" . $row['revision'];
			else $returner[] = array($row['file_id'], $row['revision']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}


	/**
	 * Get all files that are in the current review round, but have later revisions.
	 * FIXME: Move to SubmissionFileDAO
	 * @param $monographId int
	 * @param $round int
	 * @param $reviewType int
	 * @return array MonographFiles
	 */
	function &getRevisionsOfCurrentReviewFiles($monographId, $round, $reviewType = REVIEW_TYPE_INTERNAL) {
		// Get the maximum revision of each file in the review_round_files table; This is needed to avoid nested selects
		$maxRevisions = $this->retrieve(
			'SELECT rrf.file_id, MAX(revision) as revision
			FROM review_round_files rrf
			WHERE rrf.monograph_id = ? AND
				rrf.round = ? AND
				rrf.review_type = ?
			GROUP BY rrf.file_id',
			array((int) $monographId, (int) $round, (int) $reviewType)
		);

		$maxRevision = array();
		while (!$maxRevisions->EOF) {
			$row = $maxRevisions->GetRowAssoc(false);
			$maxRevision[$row['file_id']] = $row['revision'];
			$maxRevisions->MoveNext();
		}

		// Get all files attached to this monograph to compare against those in the review_round_files table
		$result =& $this->retrieve(
			'SELECT
				mf.file_id AS monograph_file_id, mf.revision as monograph_revision,
				mf.*,
				rrf.review_type, rrf.round as round
			FROM review_round_files rrf
				INNER JOIN monograph_files mf ON rrf.file_id = mf.file_id AND rrf.revision < mf.revision
				LEFT JOIN monograph_artwork_files af ON mf.file_id = af.file_id AND mf.revision = af.revision
			WHERE rrf.monograph_id = ? AND rrf.round = ? AND rrf.review_type = ?',
			array((int) $monographId, (int) $round, (int) $reviewType)
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if($maxRevision[(int)$row['file_id']] < (int)$row['revision']) $returner[] =& $this->_submissionFileDao->fromRow($row);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
