<?php

/**
 * @file classes/signoff/ProcessSignoff.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProcessSignoff
 * @ingroup signoff
 * @see ProcessSignoffDAO
 *
 * @brief Describes the signoff wrappings of a process. A process is 'signed' when all relevant parties have signed off.
 */

// $Id$

define('PROCESS_SIGNOFF_STATUS_INITIATED', 1);
define('PROCESS_SIGNOFF_STATUS_ENDED', 2);
define('PROCESS_SIGNOFF_STATUS_SIGNED', 4);
define('PROCESS_SIGNOFF_STATUS_SUSPENDED', 8); //for example

class ProcessSignoff extends DataObject {
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
	function getWorkflowProcess() {
		return $this->getData('eventType');
	}

	/**
	 * set signoff block event type.
	 * @param $eventType int
	 */
	function setWorkflowProcess($eventType) {
		return $this->setData('eventType', $eventType);
	}

	/**
	 * Get signoff block event id
	 * @return int
	 */
	function getWorkflowProcessId() {
		return $this->getData('eventId');
	}

	/**
	 * set signoff block event id
	 * @param $eventId int
	 */
	function setWorkflowProcessId($eventId) {
		return $this->setData('eventId', $eventId);
	}

}

?>