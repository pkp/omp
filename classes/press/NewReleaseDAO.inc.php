<?php

/**
 * @file classes/press/NewReleaseDAO.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewReleaseDAO
 * @ingroup press
 * @see NewRelease
 *
 * @brief Operations for setting new release status on various items.
 */

class NewReleaseDAO extends DAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get monograph IDs by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @return array monographId
	 */
	function getMonographIdsByAssoc($assocType, $assocId) {
		$returner = array();
		$result = $this->retrieve(
			'SELECT submission_id FROM new_releases WHERE assoc_type = ? AND assoc_id = ?',
			array((int) $assocType, (int) $assocId)
		);

		while (!$result->EOF) {
			list($submissionId) = $result->fields;
			$returner[$submissionId] = true;
			$result->MoveNext();
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Get monographs by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @return array Monograph
	 */
	function getMonographsByAssoc($assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT	n.submission_id
			FROM	new_releases n,
				published_submissions ps
			WHERE	n.submission_id = ps.submission_id
				AND n.assoc_type = ? AND n.assoc_id = ?
				AND ps.date_published IS NOT NULL
			ORDER BY ps.date_published DESC',
			array((int) $assocType, (int) $assocId)
		);

		$returner = array();
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		while (!$result->EOF) {
			list($submissionId) = $result->fields;
			$returner[] = $publishedSubmissionDao->getById($submissionId);
			$result->MoveNext();
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Insert a new NewRelease.
	 * @param $submissionId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function insertNewRelease($submissionId, $assocType, $assocId) {
		$this->update(
			'INSERT INTO new_releases
				(submission_id, assoc_type, assoc_id)
				VALUES
				(?, ?, ?)',
			array(
				(int) $submissionId,
				(int) $assocType,
				(int) $assocId
			)
		);
	}

	/**
	 * Delete a new release by ID.
	 * @param $submissionId int
	 * @param $pressId int optional
	 */
	function deleteByMonographId($submissionId) {
		$this->update(
			'DELETE FROM new_releases WHERE submission_id = ?',
			(int) $submissionId
		);
	}

	/**
	 * Delete a new release by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function deleteByAssoc($assocType, $assocId) {
		$this->update(
			'DELETE FROM new_releases WHERE assoc_type = ? AND assoc_id = ?',
			array((int) $assocType, (int) $assocId)
		);
	}

	/**
	 * Delete a new release.
	 * @param $submissionId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function deleteNewRelease($submissionId, $assocType, $assocId) {
		$this->update(
			'DELETE FROM new_releases
			WHERE	submission_id = ? AND
				assoc_type = ? AND
				assoc_id = ?',
			array(
				(int) $submissionId,
				(int) $assocType,
				(int) $assocId
			)
		);
	}

	/**
	 * Check if the passed monograph id is marked as new release
	 * on the passed associated object.
	 * @param $submissionId int The monograph id to check the new release state.
	 * @param $assocType int The associated object type that the monograph
	 * is checked for a new release mark.
	 * @param $assocId int The associated object id that the monograph is
	 * checked for a new release mark.
	 * @return boolean Whether or not the monograph is marked as a new release.
	 */
	function isNewRelease($submissionId, $assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT submission_id FROM new_releases WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
			array((int) $submissionId, (int) $assocType, (int) $assocId)
		);
		if ($result->RecordCount() > 0) {
			return true;
		}

		return false;
	}
}

?>
