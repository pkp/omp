<?php

/**
 * @file classes/workflow/WorkflowDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowDAO
 * @ingroup workflow
 * @see WorkflowProcess
 *
 * @brief Operations for retrieving and modifying Workflow objects.
 */

// $Id$


import('workflow.WorkflowProcess');

class WorkflowDAO extends DAO {

	/**
	 * Retrieve the next workflow process type.
	 * @param $processId int
	 * @return ProcessSignoff
	 */
	function getNextWorkflowProcess($eventType, $eventId) {
		//email relevant parties that got access
		//close off relevant processes
		//
	}
	function &getByEventType($monographId, $eventType) {
		$sql = 'SELECT * 
			FROM signoff_processes sp 
			WHERE sp.monograph_id = ? AND 
				sp.event_type = ?';

		$sqlParams = array($monographId, $eventType);

		$result =& $this->retrieve($sql, $sqlParams);

		$objDao = null;
		$returner = null;

		import('workflow.review.ReviewProcess');

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$obj =& $this->_fromRow($row);

			switch($eventType) {
			case WORKFLOW_PROCESS_TYPE_REVIEW:
				if ($obj->getProcessId() !== null) {
					$newObj = new ReviewProcess;
					$newObj->_data = $obj->_data;
					$obj = $newObj;
					unset($newObj);
				}
				break;
			default: break;
			}
			$returner[] = $obj;
			
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

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
		$processSignoff =& $this->getByEvent($monographId, $eventType, $eventId);
		if ($processSignoff) return $processSignoff;

		// Otherwise, build one.
		unset($processSignoff);
		$processSignoff = $this->newDataObject();

		$processSignoff->setStatus(WORKFLOW_PROCESS_STATUS_INITIATED);
		$processSignoff->setMonographId($monographId);
		$processSignoff->setDateInitiated(Core::getCurrentDate());
		$processSignoff->setEventType($eventType);
		$processSignoff->setEventId($eventId);

		$this->insertObject($processSignoff);
		return $processSignoff;
	}

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
	 * Construct a new data object corresponding to this DAO.
	 * @return WorkflowProcess
	 */
	function newDataObject() {
		return new WorkflowProcess();
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
				'UPDATE signoff_processes
				SET date_initiated = %s,
					date_ended = %s,
					date_signed = %s,
					monograph_id = ?,
					event_type = ?,
					event_id = ?
				WHERE process_id = ?',
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
	function &getByEvent($monographId, $eventType, $eventId) {
		$result =& $this->retrieve(
			'SELECT * 
			FROM signoff_processes 
			WHERE monograph_id = ? AND 
				event_type = ? AND 
				event_id = ?',
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