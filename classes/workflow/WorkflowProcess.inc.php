<?php

/**
 * @file classes/workflow/WorkflowProcess.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowProcess
 * @ingroup workflow
 * @see WorkflowDAO
 *
 * @brief Extend this class for workflow processes that contain signoff information.
 */

// $Id$

define('WORKFLOW_PROCESS_MONOGRAPH_PROJECT', 3);
define('WORKFLOW_PROCESS_ASSESSMENT', 0);
//define('WORKFLOW_PROCESS_TYPE_REVIEW', 2);
define('WORKFLOW_PROCESS_EDITING', 1);
define('WORKFLOW_PROCESS_EDITING_COPYEDIT', 4);
define('WORKFLOW_PROCESS_TYPE_PROOFREAD', 5);
define('WORKFLOW_PROCESS_ASSESSMENT_INTERNAL', 6);
define('WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL', 7);

define('WORKFLOW_PROCESS_STATUS_CURRENT', 2);
define('WORKFLOW_PROCESS_STATUS_INITIATED', 1);
define('WORKFLOW_PROCESS_STATUS_COMPLETE', 3);
define('WORKFLOW_PROCESS_STATUS_SUSPENDED', 8); //for example

class WorkflowProcess extends DataObject {
	//
	// Get/set methods
	//

	/**
	 * Get status.
	 * @return int
	 */
	function getStatus() {
		return $this->getData('status');
	}

	/**
	 * Get status id.
	 * @return int
	 */
	function setStatus($status) {
		return $this->setData('status', $status);
	}

	/**
	 * Get monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set monograph id.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get the start date
	 * @return int
	 */
	function getDateInitiated() {
		return $this->getData('dateInitiated');
	}

	/**
	 * set the start date
	 * @param $pressId int
	 */
	function setDateInitiated($dateInitated) {
		return $this->setData('dateInitiated', $dateInitated);
	}

	/**
	 * Get the end date.
	 * @return int
	 */
	function getDateEnded() {
		return $this->getData('dateEnded');
	}

	/**
	 * Set the end date.
	 * @param $endDate int
	 */
	function setDateEnded($endDate) {
		return $this->setData('dateEnded', $endDate);
	}

	/**
	 * Get the signed date.
	 * @return int
	 */
	function getDateSigned() {
		return $this->getData('dateSigned');
	}

	/**
	 * Set the signed date.
	 * @param $signedDate int
	 */
	function setDateSigned($signedDate) {
		return $this->setData('dateSigned', $signedDate);
	}
	/**
	 * Get signoff block event type.
	 * @return int
	 */
	function getProcessType() {
		return $this->getData('eventType');
	}

	/**
	 * set signoff block event type.
	 * @param $eventType int
	 */
	function setProcessType($eventType) {
		return $this->setData('eventType', $eventType);
	}

	/**
	 * Get signoff block event id.
	 * @return int
	 */
	function getProcessId() {
		return $this->getData('processId');
	}

	/**
	 * set signoff block event id.
	 * @param $eventType int
	 */
	function setProcessId($eventId) {
		return $this->setData('processId', $eventId);
	}

	/**
	 * Get title for this element.
	 * @return string
	 */
	function getTitle() {
		return $this->getData('title');
	}

	/**
	 * set title for this element.
	 * @param $eventType string
	 */
	function setTitle($title) {
		return $this->setData('title', $title);
	}
	function setCurrent($bool) {
		$this->setData('current', $bool);
	}
	function getCurrent() {
		return $this->getData('current');
	}
	function setSignoffQueueCount($count) {
		$this->setData('queueCount', $count);
	}
	function getSignoffQueueCount() {
		return $this->getData('queueCount');
	}
}
?>