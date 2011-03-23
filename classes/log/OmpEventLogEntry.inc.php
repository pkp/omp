<?php

/**
 * @file classes/log/OmpEventLogEntry.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpEventLogEntry
 * @ingroup log
 * @see OmpEventLogDAO
 *
 * @brief Describes an entry in the monograph history log.
 */

import('lib.pkp.classes.log.EventLogEntry');

// Information Center events
define('MONOGRAPH_LOG_NOTE_POSTED',			0x01000000);
define('MONOGRAPH_LOG_MESSAGE_SENT',			0x01000001);

class OmpEventLogEntry extends EventLogEntry {
	/**
	 * Constructor.
	 */
	function OmpEventLogEntry() {
		parent::EventLogEntry();
	}

	/**
	 * Return locale message key describing event type.
	 * @return string
	 */
	function getEventTitle() {
		switch ($this->getData('eventType')) {
			default:
				return 'submission.event.general.defaultEvent';
		}
	}
}

?>
