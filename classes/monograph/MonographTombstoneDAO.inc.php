<?php

/**
 * @file classes/submission/MonographTombstoneDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographTombstoneDAO
 * @ingroup submission
 * @see MonographTombstoneDAO
 *
 * @brief Operations for retrieving and modifying MonographTombstone objects.
 */

import ('classes.monograph.MonographTombstone');
import ('lib.pkp.classes.submission.SubmissionTombstoneDAO');

class MonographTombstoneDAO extends SubmissionTombstoneDAO {
	/**
	 * Constructor.
	 */
	function MonographTombstoneDAO() {
		parent::SubmissionTombstoneDAO();
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return MonographTombstone
	 */
	function newDataObject() {
		return new MonographTombstone();
	}

	/**
	 * @see lib/pkp/classes/submission/SubmissionTombstoneDAO::getById()
	 * @param $tombstoneId int
	 * @param $journalId int
	 */
	function &getById($tombstoneId, $pressId = null) {
		return parent::getById($tombstoneId, $pressId, 'press_id');
	}

	/**
	 * @see lib/pkp/classes/submission/SubmissionTombstoneDAO::_fromRow()
	 */
	function &_fromRow($row) {
		$monographTombstone =& parent::_fromRow($row);
		$monographTombstone->setPressId($row['press_id']);
		$monographTombstone->setSeriesId($row['series_id']);

		HookRegistry::call('MonographTombstoneDAO::_fromRow', array(&$monographTombstone, &$row));

		return $monographTombstone;
	}

	/**
	 * Inserts a new monograph tombstone into submission_tombstones table.
	 * @param $monographTombstone MonographTombstone
	 * @return int Monograph tombstone id.
	 */
	function insertObject(&$monographTombstone) {
		$this->update(
			sprintf('INSERT INTO submission_tombstones
				(press_id, submission_id, date_deleted, series_id, set_spec, set_name, oai_identifier)
				VALUES
				(?, ?, %s, ?, ?, ?, ?)',
				$this->datetimeToDB(date('Y-m-d H:i:s'))
			),
			array(
				(int) $monographTombstone->getPressId(),
				(int) $monographTombstone->getSubmissionId(),
				(int) $monographTombstone->getSeriesId(),
				$monographTombstone->getSetSpec(),
				$monographTombstone->getSetName(),
				$monographTombstone->getOAIIdentifier()
			)
		);

		$monographTombstone->setId($this->getInsertTombstoneId());

		return $monographTombstone->getId();
	}

	/**
	 * Update a monograph tombstone in the submission_tombstones table.
	 * @param $monographTombstone MonographTombstone
	 * @return int monograph tombstone id
	 */
	function updateObject(&$monographTombstone) {
		$returner = $this->update(
			sprintf('UPDATE	submission_tombstones SET
					press_id = ?,
					submission_id = ?,
					date_deleted = %s,
					series_id = ?,
					set_spec = ?,
					set_name = ?,
					oai_identifier = ?
					WHERE	tombstone_id = ?',
				$this->datetimeToDB(date('Y-m-d H:i:s'))
			),
			array(
				(int) $monographTombstone->getPressId(),
				(int) $monographTombstone->getSubmissionId(),
				(int) $monographTombstone->getSeriesId(),
				$monographTombstone->getSetSpec(),
				$monographTombstone->getSetName(),
				$monographTombstone->getOAIIdentifier(),
				(int) $monographTombstone->getId()
			)
		);

		return $returner;
	}

	/**
	 * @see lib/pkp/classes/submission/SubmissionTombstoneDAO::deleteById()
	 * @param $tombstoneId int
	 * @param $pressId int
	 */
	function deleteById($tombstoneId, $pressId = null) {
		return parent::deleteById($tombstoneId, $pressId, 'press_id');
	}

	/**
	 * @see lib/pkp/classes/submission/SubmissionTombstoneDAO::getSets()
	 * @param $pressId int
	 */
	function &getSets($pressId) {
		return parent::getSets($pressId, 'press_id');
	}
}

?>