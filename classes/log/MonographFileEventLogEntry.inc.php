<?php

/**
 * @file classes/log/MonographFileEventLogEntry.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEventLogEntry
 * @ingroup log
 * @see MonographFileEventLogDAO
 *
 * @brief Describes an entry in the monograph file history log.
 */

import('classes.log.OmpEventLogEntry');

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

	/**
	 * Return locale message key describing event type.
	 * @return string
	 */
	function getEventTitle() {
		switch ($this->getData('eventType')) {
			default:
				return parent::getEventTitle();
		}
	}
}

?>
