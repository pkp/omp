<?php

/**
 * @file classes/log/MonographEventLogEntry.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEventLogEntry
 * @ingroup log
 * @see MonographEventLogDAO
 *
 * @brief Describes an entry in the monograph history log.
 */

import('classes.log.OmpEventLogEntry');

/**
 * Log entry event types. All types must be defined here.
 */
// General events					0x10000000
define('MONOGRAPH_LOG_MONOGRAPH_SUBMIT',		0x10000001);
define('MONOGRAPH_LOG_METADATA_UPDATE',			0x10000002);
define('MONOGRAPH_LOG_ADD_PARTICIPANT',			0x10000003);
define('MONOGRAPH_LOG_REMOVE_PARTICIPANT',		0x10000004);

define('MONOGRAPH_LOG_METADATA_PUBLISH',		0x10000006);
define('MONOGRAPH_LOG_METADATA_UNPUBLISH',		0x10000007);
define('MONOGRAPH_LOG_PUBLICATION_FORMAT_PUBLISH',	0x10000008);
define('MONOGRAPH_LOG_PUBLICATION_FORMAT_UNPUBLISH',	0x10000009);
define('MONOGRAPH_LOG_CATALOG_METADATA_UPDATE',	0x10000010);
define('MONOGRAPH_LOG_PUBLICATION_FORMAT_METADATA_UPDATE',	0x10000011);

// Editor events

define('MONOGRAPH_LOG_EDITOR_DECISION',			0x30000003);

// Reviewer events					0x40000000
define('MONOGRAPH_LOG_REVIEW_ASSIGN',			0x40000001);

define('MONOGRAPH_LOG_REVIEW_ACCEPT',			0x40000006);
define('MONOGRAPH_LOG_REVIEW_DECLINE',			0x40000007);

define('MONOGRAPH_LOG_REVIEW_SET_DUE_DATE',		0x40000011);

define('MONOGRAPH_LOG_REVIEW_CLEAR',			0x40000014);

// Deletion of the last revision of a file
define('MONOGRAPH_LOG_LAST_REVISION_DELETED', 	0x50000003);

// Production events
define('MONOGRAPH_LOG_PROOFS_APPROVED',		0x50000008);

class MonographEventLogEntry extends OmpEventLogEntry {
	/**
	 * Constructor.
	 */
	function MonographEventLogEntry() {
		parent::OmpEventLogEntry();
	}


	//
	// Getters/setters
	//
	/**
	 * Set the monograph ID
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setAssocId($monographId);
	}


	/**
	 * Get the monograph ID
	 * @return int
	 */
	function getMonographId() {
		return $this->getAssocId();
	}


	/**
	 * Get the assoc ID
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_MONOGRAPH;
	}
}

?>
