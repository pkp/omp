<?php

/**
 * @file classes/log/MonographFileEventLogEntry.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEventLogEntry
 * @ingroup log
 * @see MonographFileEventLogDAO
 *
 * @brief Describes an entry in the monograph file history log.
 */

import('classes.log.OmpEventLogEntry');


// File upload/delete event types.
define('MONOGRAPH_LOG_FILE_UPLOAD',	0x50000001);
define('MONOGRAPH_LOG_FILE_DELETE',	0x50000002);
define('MONOGRAPH_LOG_FILE_REVISION_UPLOAD',	0x50000008);
define('MONOGRAPH_LOG_FILE_REVISION_DELETE',	0x50000009);

// Audit events
define('MONOGRAPH_LOG_FILE_AUDITOR_ASSIGN',		0x50000004);
define('MONOGRAPH_LOG_FILE_AUDITOR_CLEAR',		0x50000005);
define('MONOGRAPH_LOG_FILE_AUDIT_UPLOAD', 		0x50000006);
define('MONOGRAPH_LOG_FILE_SIGNOFF_SIGNOFF', 	0x50000007);

class MonographFileEventLogEntry extends OmpEventLogEntry {
	/**
	 * Constructor.
	 */
	function MonographFileEventLogEntry() {
		parent::OmpEventLogEntry();
	}

	function setFileId($fileId) {
		return $this->setAssocId($fileId);
	}

	function getFileId() {
		return $this->getAssocId();
	}
}

?>
