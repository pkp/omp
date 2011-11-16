<?php

/**
 * @file classes/log/MonographEmailLogDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEmailLogDAO
 * @ingroup log
 * @see EmailLogDAO
 *
 * @brief Extension to EmailLogDAO for monograph-specific log entries.
 */

import('lib.pkp.classes.log.EmailLogDAO');
import('classes.log.MonographEmailLogEntry');

class MonographEmailLogDAO extends EmailLogDAO {
	/**
	 * Constructor
	 */
	function MonographEmailLogDAO() {
		parent::EmailLogDAO();
	}

	/**
	 * Instantiate and return a MonographEmailLogEntry
	 * @return MonographEmailLogEntry
	 */
	function newDataObject() {
		$returner = new MonographEmailLogEntry();
		$returner->setAssocType(ASSOC_TYPE_MONOGRAPH);
		return $returner;
	}

	/**
	 * Get monograph email log entries by monograph ID and event type
	 * @param $monographId int
	 * @param $eventType MONOGRAPH_EMAIL_...
	 * @return DAOResultFactory
	 */
	function getByEventType($monographId, $eventType) {
		return parent::getByEventType(ASSOC_TYPE_MONOGRAPH, $monographId, $eventType);
	}
}

?>
