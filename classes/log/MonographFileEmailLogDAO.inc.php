<?php

/**
 * @file classes/log/MonographFileEmailLogDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEmailLogDAO
 * @ingroup log
 * @see EmailLogDAO
 *
 * @brief Extension to EmailLogDAO for monograph file specific log entries.
 */


import('lib.pkp.classes.log.EmailLogDAO');
import('classes.log.MonographFileEmailLogEntry');

class MonographFileEmailLogDAO extends EmailLogDAO {
	function MonographFileEmailLogDAO() {
		parent::EmailLogDAO();
	}

	function newDataObject() {
		$returner = new MonographFileEmailLogEntry();
		$returner->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
		return $returner;
	}

	function getByEventType($fileId, $eventType) {
		return parent::getByEventType(ASSOC_TYPE_MONOGRAPH_FILE, $fileId, $eventType);
	}
}

?>
