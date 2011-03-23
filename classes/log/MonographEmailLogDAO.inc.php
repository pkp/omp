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
	function MonographEmailLogDAO() {
		parent::EmailLogDAO();
	}

	function newDataObject() {
		$returner = new MonographEmailLogEntry();
		$returner->setAssocType(ASSOC_TYPE_MONOGRAPH);
		return $returner;
	}

	function getByEventType($monographId, $eventType) {
		return $this->getByEventType(ASSOC_TYPE_MONOGRAPH, $monographId, $eventType);
	}
}

?>
