<?php

/**
 * @file classes/log/MonographEventLogDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEventLogDAO
 * @ingroup log
 * @see EventLogDAO
 *
 * @brief Extension to EventLogDAO for monograph-specific log entries.
 */

import('lib.pkp.classes.log.EventLogDAO');
import('classes.log.MonographEventLogEntry');

class MonographEventLogDAO extends EventLogDAO {
	/**
	 * Constructor
	 */
	function MonographEventLogDAO() {
		parent::EventLogDAO();
	}

	/**
	 * Generate a new DataObject
	 * @return MonographEventLogEntry
	 */
	function newDataObject() {
		$returner = new MonographEventLogEntry();
		$returner->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
		return $returner;
	}

	/**
	 * Get monograph event log entries by monograph ID
	 * @param $monographId int
	 * @return DAOResultFactory
	 */
	function &getByMonographId($monographId) {
		$returner =& $this->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monographId);
		return $returner;
	}
}

?>
