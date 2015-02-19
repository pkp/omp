<?php

/**
 * @file classes/press/NewReleaseDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	function NewReleaseDAO() {
		parent::DAO();
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
			list($monographId) = $result->fields;
			$returner[$monographId] = true;
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
		$returner = array();
		$result = $this->retrieve(
			'SELECT	n.submission_id
			FROM	new_releases n,
				published_submissions ps
			WHERE	n.submission_id = ps.submission_id AND
				n.assoc_type = ? AND n.assoc_id = ?
			ORDER BY ps.date_published DESC',
			array((int) $assocType, (int) $assocId)
		);

		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		while (!$result->EOF) {
			list($monographId) = $result->fields;
			$returner[] = $publishedMonographDao->getById($monographId);
			$result->MoveNext();
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Insert a new NewRelease.
	 * @param $monographId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function insertNewRelease($monographId, $assocType, $assocId) {
		$this->update(
			'INSERT INTO new_releases
				(submission_id, assoc_type, assoc_id)
				VALUES
				(?, ?, ?)',
			array(
				(int) $monographId,
				(int) $assocType,
				(int) $assocId
			)
		);
	}

	/**
	 * Delete a new release by ID.
	 * @param $monographId int
	 * @param $pressId int optional
	 */
	function deleteByMonographId($monographId) {
		$this->update(
			'DELETE FROM new_releases WHERE submission_id = ?',
			(int) $monographId
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
	 * @param $monographId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function deleteNewRelease($monographId, $assocType, $assocId) {
		$this->update(
			'DELETE FROM new_releases
			WHERE	submission_id = ? AND
				assoc_type = ? AND
				assoc_id = ?',
			array(
				(int) $monographId,
				(int) $assocType,
				(int) $assocId
			)
		);
	}
}

?>
