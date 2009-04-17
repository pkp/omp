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
	function getEnabledObjects($eventType) {
		$returner = null;

		switch ($eventType) {
		case WORKFLOW_PROCESS_TYPE_REVIEW:
			$returner[WORKFLOW_PROCESS_TYPE_REVIEW_INTERNAL] = 'Internal Review';
			$returner[WORKFLOW_PROCESS_TYPE_REVIEW_EXTERNAL] = 'External Review';
		default:break;
		}

		return $returner;
	}
	function getCurrent($eventType) {
		$result =& $this->retrieve(
				'SELECT *
				FROM signoff_processes
				WHERE event_type = ? AND event_id IS NOT NULL AND status = ' . WORKFLOW_PROCESS_STATUS_CURRENT,
				$eventType
			);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}
	function &getByEventType($monographId, $eventType) {
		$returner = null;
		$sql = 'SELECT * 
			FROM signoff_processes sp 
			WHERE sp.monograph_id = ? AND 
				sp.event_type = ?';

		$sqlParams = array($monographId, $eventType);

		$result =& $this->retrieve($sql, $sqlParams);

		$enabledObjects = $this->getEnabledObjects($eventType);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$obj =& $this->_fromRow($row);
			if (isset($enabledObjects[$obj->getProcessId()])) {
				$obj->setTitle($enabledObjects[$obj->getProcessId()]);
				unset($enabledObjects[$obj->getProcessId()]);
			}
			$returner[] = $obj;
			$result->MoveNext();
		}
		foreach ($enabledObjects as $eo) {
			$obj = $this->newDataObject();
			$obj->setTitle($eo);
			$returner[] = $obj;
		}
		foreach ($returner as $process) {
			if ($process->getDateInitiated() == null) {
				$process->setCurrentProcess(true);
				break;
			}
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
		$workflowProcess =& $this->getByEvent($monographId, $eventType, $eventId);
		if ($workflowProcess) return $workflowProcess;

		// Otherwise, build one.
		unset($workflowProcess);
		$workflowProcess = $this->newDataObject();

		$workflowProcess->setStatus(WORKFLOW_PROCESS_STATUS_INITIATED);
		$workflowProcess->setMonographId($monographId);
		$workflowProcess->setDateInitiated(Core::getCurrentDate());
		$workflowProcess->setProcessType($eventType);
		$workflowProcess->setProcessId($eventId);

		$this->insertObject($workflowProcess);
		return $workflowProcess;
	}

	/**
	 * Retrieve a signoff entity by ID.
	 * @param $workflowProcessId int
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
		$workflowProcess = $this->newDataObject();

		$workflowProcess->setId($row['process_id']);
		$workflowProcess->setStatus($row['status']);
		$workflowProcess->setMonographId($row['monograph_id']);
		$workflowProcess->setDateInitiated($row['date_initiated']);
		$workflowProcess->setDateEnded($row['date_ended']);
		$workflowProcess->setDateSigned($row['date_signed']);
		$workflowProcess->setProcessType($row['event_type']);
		$workflowProcess->setProcessId($row['event_id']);

		return $workflowProcess;
	}

	/**
	 * Insert a new Signoff.
	 * @param $signoff Signoff
	 * @return int 
	 */
	function insertObject(&$workflowProcess) {
		$this->update(
				'INSERT INTO signoff_processes
				(monograph_id, date_initiated, status, date_ended, event_type, event_id)
				VALUES
				(?, ?, ?, ?, ?, ?)',
			array(
				$workflowProcess->getMonographId(),
				$workflowProcess->getDateInitiated(),
				$workflowProcess->getStatus(),
				$workflowProcess->getDateEnded(),
				$workflowProcess->getProcessType(),
				$workflowProcess->getProcessId()
			)
		);
		$workflowProcess->setId($this->getInsertId());
		return $workflowProcess->getId();
	}

	/**
	 * Update an existing signoff entity entry.
	 * @param $workflowProcess SignoffEntity
	 * @return boolean
	 */
	function updateObject(&$workflowProcess) {
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
				$this->datetimeToDB($workflowProcess->getDateInitiated()),
				$this->datetimeToDB($workflowProcess->getDateEnded()),
				$this->datetimeToDB($workflowProcess->getDateSigned())
			),
			array(
				$workflowProcess->getMonographId(),
				$workflowProcess->getProcessType(),
				$workflowProcess->getProcessId(),
				$workflowProcess->getId()
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

		$sql = 'SELECT *
			FROM signoff_processes
			WHERE monograph_id = ? AND
				event_type = ? AND ';

		$sqlParams = array($monographId, (int) $eventType);

		if ($eventId == null) {
			$sql .= 'event_id IS NULL';
		} else {
			$sql .= 'event_id = ?';
			$sqlParams[] = $eventId;
		}

		$result =& $this->retrieve($sql, $sqlParams);

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