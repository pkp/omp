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
	 * Retrieve a WorkflowElements by workflow element type.
	 * @param $monographId int
	 * @param $eventType int
	 * @return ProcessSignoff
	 */
	function &getByWorkflowProcessType($monographId, $eventType) {

		$returner = null;

		switch ($eventType) {
		case WORKFLOW_PROCESS_TYPE_REVIEW:
			$reviewTypeDao =& DAORegistry::getDAO('ReviewProcessDAO');
			$returner =& $reviewTypeDao->getEnabledObjects($monographId);
			return $returner;
		}

		return $returner;

	}

	/**
	 * Retrieve the ordered workflow process list.
	 * @return array
	 */
	function &getWorkflowProcesses($subtree = null) {
		$processes = array(
				array(),
				array(),
				array()
			);

		if ($subtree != null) {
			if (isset($processes[$subtree]))
			    return $processes[$subtree];
		}
		return $processes;
	}

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
	function &getSignoffSummary($monographId, $eventType) {
		$sql = 'SELECT * FROM (SELECT * FROM signoff_processes WHERE event_type = ?';
		$sqlParams = array($eventType);

		switch ($eventType) {
		case SIGNOFF_EVENT_TYPE_REVIEW_PROCESS:
			$sql .= ' UNION ALL SELECT * FROM signoff_processes WHERE event_type = '.SIGNOFF_EVENT_TYPE_REVIEW;
			$reviewTypeDao =& DAORegistry::getDAO('ReviewTypeDAO');
			$reviewTypes =& $reviewTypeDao->getEnabledObjects();
			$simpleReturner = false;
			break;
		case SIGNOFF_EVENT_TYPE_EDITING_PROCESS:
			$sql .= ' UNION ALL SELECT * FROM signoff_processes WHERE event_type = '.SIGNOFF_EVENT_TYPE_COPYEDIT;
			$simpleReturner = false;
			break;
		}
		$sql .= ') AS events WHERE events.monograph_id = ?';
		// then construct tree
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
		$processSignoff->setEventType($eventType);
		$processSignoff->setEventId($eventId);

		$this->insertObject($processSignoff);
		return $processSignoff;
	}

	/**
	 * Retrieve an array of signoffs matching the specified
	 * symbolic name and assoc info.
	 * @param $symbolic string
	 * @param $assocType int
	 * @param $assocId int
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