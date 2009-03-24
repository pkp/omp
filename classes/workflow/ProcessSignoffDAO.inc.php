<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

// $Id$


import('workflow.ProcessSignoff');

class ProcessSignoffDAO extends DAO {

	/**
	 * Retrieve a signoff entity by ID.
	 * @param $processSignoffId int
	 * @return SignoffEntity
	 */
	function getById($processId) {

		$result =& $this->retrieve(
			'SELECT * FROM signoff_processes WHERE process_id = ?',
				$processId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Fetch a process, building it if needed.
	 * @param $monographId int
	 * @param $eventType int
	 * @param $eventId int
	 * @return ProcessSignoff
	 */
	function build($monographId, $eventType, $eventId) {
		// If one exists, fetch and return.
		$processSignoff =& $this->getProcessSignoff($monographId, $eventType, $eventId);
		if ($processSignoff) return $processSignoff;

		// Otherwise, build one.
		unset($processSignoff);
		$processSignoff = $this->newDataObject();

		$processSignoff->setStatus(PROCESS_SIGNOFF_STATUS_INITIATED);
		$processSignoff->setMonographId($monographId);
		$processSignoff->setDateInitiated(Core::getCurrentDate());
		$processSignoff->setWorkflowProcess($eventType);
		$processSignoff->setWorkflowProcessId($eventId);

		$this->insertObject($processSignoff);
		return $processSignoff;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntity
	 */
	function newDataObject() {
		return new ProcessSignoff();
	}

	/**
	 * Internal function to return an ProcessSignoff object from a row.
	 * @param $row array
	 * @return ProcessSignoff
	 */
	function _fromRow(&$row) {
		$processSignoff = $this->newDataObject();

		$processSignoff->setId($row['process_id']);
		$processSignoff->setStatus($row['status']);
		$processSignoff->setMonographId($row['monograph_id']);
		$processSignoff->setDateInitiated($row['date_initiated']);
		$processSignoff->setDateEnded($row['date_ended']);
		$processSignoff->setDateSigned($row['date_signed']);
		$processSignoff->setWorkflowProcess($row['event_type']);
		$processSignoff->setWorkflowProcessId($row['event_id']);

		return $processSignoff;
	}

	/**
	 * Insert a new Signoff.
	 * @param $signoff Signoff
	 * @return int 
	 */
	function insertObject(&$processSignoff) {
		$this->update(
				'INSERT INTO signoff_processes
				(monograph_id, date_initiated, status, date_ended, event_type, event_id)
				VALUES
				(?, ?, ?, ?, ?, ?)',
			array(
				$processSignoff->getMonographId(),
				$processSignoff->getDateInitiated(),
				$processSignoff->getStatus(),
				$processSignoff->getDateEnded(),
				$processSignoff->getWorkflowProcess(),
				$processSignoff->getWorkflowProcessId()
			)
		);
		$processSignoff->setId($this->getInsertId());
		return $processSignoff->getId();
	}

	/**
	 * Update an existing signoff entity entry.
	 * @param $processSignoff SignoffEntity
	 * @return boolean
	 */
	function updateObject(&$processSignoff) {
		$returner = $this->update(
			sprintf(
				'UPDATE	signoff_processes
				SET	date_initiated = %s,
					date_ended = %s,
					date_signed = %s,
					monograph_id = ?,
					event_type = ?,
					event_id = ?
				WHERE	process_id = ?',
				$this->datetimeToDB($processSignoff->getDateInitiated()),
				$this->datetimeToDB($processSignoff->getDateEnded()),
				$this->datetimeToDB($processSignoff->getDateSigned())
			),
			array(
				$processSignoff->getMonographId(),
				$processSignoff->getWorkflowProcess(),
				$processSignoff->getWorkflowProcessId(),
				$processSignoff->getId()
			)
		);
		return $returner;
	}

	/**
	 * Retrieve an array of signoffs matching the specified
	 * symbolic name and assoc info.
	 * @param $monographId int
	 * @param $eventType int
	 * @param $eventId int
	 */
	function &getProcessSignoff($monographId, $eventType, $eventId) {
		$result =& $this->retrieve(
			'SELECT * FROM signoff_processes WHERE monograph_id = ? AND event_type = ? AND event_id = ?',
			array($monographId, (int) $eventType, $eventId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the ID of the last inserted signoff process.
	 * @return int
	 */
	function getInsertId() {
		return parent::getInsertId('signoff_processes', 'process_id');
	}

}
?>