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
	 * @param $currentProcess WorkflowProcess
	 * @return WorkflowProcess
	 */
	function &_getWorkflowStructure() {

		$workflow[WORKFLOW_PROCESS_ASSESSMENT] = array(
								WORKFLOW_PROCESS_ASSESSMENT_INTERNAL, 
								WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL
							);
		$workflow[WORKFLOW_PROCESS_EDITING] = array(
								WORKFLOW_PROCESS_EDITING_COPYEDIT
							);
		return $workflow;
	}

	/**
	 * Retrieve the next workflow process type.
	 * @param $currentProcess WorkflowProcess
	 * @return WorkflowProcess
	 */
	function proceed($monographId) {
		//FIXME: email relevant parties that got access

		$press =& Request::getPress();

		$workflow =& $this->_getWorkflowStructure();

		$currentProcess =& $this->_getCurrent($monographId);

		if ($currentProcess == null) {

			$this->build($monographId,
					WORKFLOW_PROCESS_ASSESSMENT,
					null
				);

			return $this->build($monographId, 
					WORKFLOW_PROCESS_ASSESSMENT, 
					WORKFLOW_PROCESS_ASSESSMENT_INTERNAL, 
					WORKFLOW_PROCESS_STATUS_CURRENT
				);
		}

		//defer update?
		if ($currentProcess->getDateEnded() == null) {
			$currentProcess->setDateEnded(Core::getCurrentDate());
			$this->updateObject($currentProcess);
		}

		$signoffEntityDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$incompleteSignoffs =& $signoffEntityDao->getRequiredSignoffsByProcess($currentProcess->getProcessType(),
										$currentProcess->getProcessId(),
										$press->getId()
									);
		if (isset($incompleteSignoffs)) {
			// send email to people scheduled to signoff
			return null;
		}
		

		$processType = $currentProcess->getProcessType();
		$processId = $currentProcess->getProcessId();

		$workflowKeys = array_keys($workflow);

		// parse the $workflow structure
		// FIXME: shortcut it with the constants
		for ($j=0,$jcount=count($workflow); $j<$jcount; $j++) {
			for ($i=0,$count=count($workflow[$j]); $i<$count; $i++) {
				if ($workflow[$j][$i] == $processId) {

					$process =& $this->build($monographId, $processType, $processId);
					$process->setStatus(WORKFLOW_PROCESS_STATUS_COMPLETE);
					$process->setDateSigned(Core::getCurrentDate());
					$this->updateObject($process);

					if ($i == $count-1) {
						$process =& $this->build($monographId, $processType, null);
						$process->setDateSigned(Core::getCurrentDate());
						$process->setStatus(WORKFLOW_PROCESS_STATUS_COMPLETE);
						$this->updateObject($process);

						if (isset($workflow[$j+1])) {
							$this->build($monographId, $workflowKeys[$j+1], null);
							if (isset($workflow[$j+1][0])) {
								return $this->build($monographId, $workflowKeys[$j+1], $workflow[$j+1][0], WORKFLOW_PROCESS_STATUS_CURRENT);
							} else {
								die('Unexpected Workflow Node');
							}
						}
					} else {
						return $this->build($monographId, 
									$processType, 
									$workflow[$j][$i+1], 
									WORKFLOW_PROCESS_STATUS_CURRENT
								);
					}
				}
			}
		}
		return null;
	}

	function &_getCurrent($monographId) {
		$result =& $this->retrieve(
				'SELECT *
				FROM signoff_processes
				WHERE monograph_id = ? AND 
					event_id IS NOT NULL AND 
					status = ' . WORKFLOW_PROCESS_STATUS_CURRENT . '
				LIMIT 1',
				$monographId
			);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	function _getTitleByProcessId($processId) {
		switch ($processId) {
		case WORKFLOW_PROCESS_ASSESSMENT_INTERNAL:
			return 'Internal Review';
		case WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL:
			 return 'External Review';
		default: return '';
		}
	}

	function &getByEventType($monographId, $eventType) {
		$returner = null;
		$sql = 'SELECT * 
			FROM signoff_processes sp 
			WHERE sp.monograph_id = ? AND 
				sp.event_id IS NOT NULL AND
				sp.event_type = ?';

		$sqlParams = array($monographId, $eventType);

		$result =& $this->retrieve($sql, $sqlParams);

		$workflow =& $this->_getWorkflowStructure();

		if (!isset($workflow[$eventType])) {
			return $returner;
		} else {
			$workflowProcesses = $workflow[$eventType];
		}

		$signoffEntityDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$press =& Request::getPress();

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$obj =& $this->_fromRow($row);

			if (in_array($obj->getProcessId(), $workflowProcesses)) {
				$key = array_keys($workflowProcesses, $obj->getProcessId());
				$key = $key[0];

				if ($obj->getStatus() == WORKFLOW_PROCESS_STATUS_CURRENT) {
					$users =& $signoffEntityDao->getRequiredSignoffsByProcess(
											WORKFLOW_PROCESS_ASSESSMENT,
											$obj->getProcessId(),
											$press->getId()
										);
					$obj->setSignoffQueueCount(count($users));
					unset($users);
				}
				$obj->setTitle($this->_getTitleByProcessId($obj->getProcessId()));
				$returner[$key] = $obj;
			}

			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		for ($i=0, $count=count($workflowProcesses); $i<$count; $i++) {
			if (!isset($returner[$i])) {
				$obj = $this->newDataObject();
				$obj->setTitle($this->_getTitleByProcessId($workflowProcesses[$i]));
				$returner[$i] = $obj;
			}
		}

		return $returner;
	}

	/**
	 * Fetch a process, building it if needed.
	 * @param $monographId int
	 * @param $eventType int
	 * @param $eventId int
	 * @return ProcessSignoff
	 */
	function build($monographId, $eventType, $eventId, $status = WORKFLOW_PROCESS_STATUS_INITIATED) {
		// If one exists, fetch and return.
		$workflowProcess =& $this->getByEvent($monographId, $eventType, $eventId);
		if ($workflowProcess) return $workflowProcess;

		// Otherwise, build one.
		unset($workflowProcess);
		$workflowProcess = $this->newDataObject();

		$workflowProcess->setStatus($status);
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
					event_id = ?,
					status = ?
				WHERE process_id = ?',
				$this->datetimeToDB($workflowProcess->getDateInitiated()),
				$this->datetimeToDB($workflowProcess->getDateEnded()),
				$this->datetimeToDB($workflowProcess->getDateSigned())
			),
			array(
				$workflowProcess->getMonographId(),
				$workflowProcess->getProcessType(),
				$workflowProcess->getProcessId(),
				$workflowProcess->getStatus(),
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