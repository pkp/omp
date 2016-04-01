<?php

/**
 * @file classes/press/FeatureDAO.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FeatureDAO
 * @ingroup press
 * @see Feature
 *
 * @brief Operations for setting Featured status on various items.
 */

class FeatureDAO extends DAO {
	/**
	 * Constructor
	 */
	function FeatureDAO() {
		parent::DAO();
	}

	/**
	 * Get monograph IDs by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @return array Associative array seq => monograph ID
	 */
	function getMonographIdsByAssoc($assocType, $assocId) {
		$returner = array();
		$result = $this->retrieve(
			'SELECT submission_id, seq FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
			array((int) $assocType, (int) $assocId)
		);

		while (!$result->EOF) {
			list($monographId, $seq) = $result->fields;
			$returner[$seq] = $monographId;
			$result->MoveNext();
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Get feature sequences by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @return array Associative array monograph ID => seq
	 */
	function getSequencesByAssoc($assocType, $assocId) {
		return array_flip($this->getMonographIdsByAssoc($assocType, $assocId));
	}

	/**
	 * Insert a new feature.
	 * @param $monographId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @param $seq int
	 */
	function insertFeature($monographId, $assocType, $assocId, $seq) {
		$this->update(
			'INSERT INTO features
				(submission_id, assoc_type, assoc_id, seq)
				VALUES
				(?, ?, ?, ?)',
			array(
				(int) $monographId,
				(int) $assocType,
				(int) $assocId,
				(int) $seq
			)
		);
	}

	/**
	 * Delete a feature by ID.
	 * @param $featureId int
	 * @param $pressId int optional
	 */
	function deleteByMonographId($monographId) {
		$this->update(
			'DELETE FROM features WHERE submission_id = ?',
			(int) $monographId
		);
	}

	/**
	 * Delete a feature by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function deleteByAssoc($assocType, $assocId) {
		$this->update(
			'DELETE FROM features WHERE assoc_type = ? AND assoc_id = ?',
			array((int) $assocType, (int) $assocId)
		);
	}

	/**
	 * Delete a feature.
	 * @param $monographId int
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 */
	function deleteFeature($monographId, $assocType, $assocId) {
		$this->update(
			'DELETE FROM features
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

	/**
	 * Check if the passed monograph id is featured on the
	 * passed associated object.
	 * @param $monographId int The monograph id to check the feature state.
	 * @param $assocType int The associated object type that the monograph
	 * is featured.
	 * @param $assocId int The associated object id that the monograph is
	 * featured.
	 * @return boolean Whether or not the monograph is featured.
	 */
	function isFeatured($monographId, $assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT submission_id FROM features WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
			array((int) $monographId, (int) $assocType, (int) $assocId)
		);
		if ($result->RecordCount() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get the current sequence position of the passed monograph id.
	 * @param $monographId int The monograph id to check the sequence position.
	 * @param $assocType int The monograph associated object type.
	 * @param $assocId int The monograph associated object id.
	 * @return int or boolean The monograph sequence position or false if no
	 * monograph feature is set.
	 */
	function getSequencePosition($monographId, $assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT seq FROM features WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
			array((int) $monographId, (int) $assocType, (int) $assocId)
		);
		if ($result->RecordCount() > 0) {
			return current($result->fields);
		}

		return false;
	}

	function setSequencePosition($monographId, $assocType, $assocId, $sequencePosition) {
		$this->update(
			'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
			array((int) $sequencePosition, (int) $monographId, (int) $assocType, (int) $assocId)
		);
	}

	/**
	 * Resequence features by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int per $assocType
	 * @param $seqMonographId if specified, sequence of monograph to return
	 * @return array Associative array of id => seq for resequenced set
	 */
	function resequenceByAssoc($assocType, $assocId) {
		$returner = array();
		$result = $this->retrieve(
			'SELECT submission_id FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
			array((int) $assocType, (int) $assocId)
		);

		for ($i=2; !$result->EOF; $i+=2) {
			list($monographId) = $result->fields;
			$this->update(
				'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
				array(
					$i,
					$monographId,
					(int) $assocType,
					(int) $assocId
				)
			);
			$returner[$monographId] = $i;

			$result->MoveNext();
		}

		$result->Close();
		return $returner;
	}
}

?>
