<?php

/**
 * @file classes/press/FeatureDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class FeatureDAO
 * @ingroup press
 * @see Feature
 *
 * @brief Operations for setting Featured status on various items.
 */

class FeatureDAO extends DAO {
	/**
	 * Get monograph IDs by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int
	 * @return array Associative array seq => monograph ID
	 */
	function getMonographIdsByAssoc($assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT submission_id, seq FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
			[(int) $assocType, (int) $assocId]
		);

		$returner = [];
		foreach ($result as $row) {
			$returner[$row->seq] = $row->submission_id;
		}
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
			[
				(int) $monographId,
				(int) $assocType,
				(int) $assocId,
				(int) $seq
			]
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
			[(int) $monographId]
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
			[(int) $assocType, (int) $assocId]
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
			[
				(int) $monographId,
				(int) $assocType,
				(int) $assocId
			]
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
			[(int) $monographId, (int) $assocType, (int) $assocId]
		);
		return (boolean) $result->current();
	}

	/**
	 * Return the monograph's featured settings in all assoc types
	 * @param $monographId int The monograph id to get the feature state.
	 * @return array
	 */
	function getFeaturedAll($monographId) {
		$result = $this->retrieve(
			'SELECT assoc_type, assoc_id, seq FROM features WHERE submission_id = ?',
			[(int) $monographId]
		);

		$featured = [];
		foreach ($result as $row) {
			$featured[] = array(
				'assoc_type' => (int) $row->assoc_type,
				'assoc_id' => (int) $row->assoc_id,
				'seq' => (int) $row->seq,
			);
		}
		return $featured;
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
			[(int) $monographId, (int) $assocType, (int) $assocId]
		);
		$row = $result->current();
		return $row ? $row->seq : false;
	}

	function setSequencePosition($monographId, $assocType, $assocId, $sequencePosition) {
		$this->update(
			'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
			[(int) $sequencePosition, (int) $monographId, (int) $assocType, (int) $assocId]
		);
	}

	/**
	 * Resequence features by association.
	 * @param $assocType int ASSOC_TYPE_...
	 * @param $assocId int per $assocType
	 * @return array Associative array of id => seq for resequenced set
	 */
	function resequenceByAssoc($assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT submission_id FROM features WHERE assoc_type = ? AND assoc_id = ? ORDER BY seq',
			[(int) $assocType, (int) $assocId]
		);

		$returner = [];
		foreach ($result as $key => $value) {
			$this->update(
				'UPDATE features SET seq = ? WHERE submission_id = ? AND assoc_type = ? AND assoc_id = ?',
				[
					$key + 1,
					$value->submission_id,
					(int) $assocType,
					(int) $assocId
				]
			);
			$returner[$value->submission_id] = $key;
		}
		return $returner;
	}
}


