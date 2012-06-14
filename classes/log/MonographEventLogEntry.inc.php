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

define('MONOGRAPH_LOG_MONOGRAPH_PUBLISH',		0x10000006);
define('MONOGRAPH_LOG_MONOGRAPH_UNPUBLISH',		0x10000007);

// Editor events

define('MONOGRAPH_LOG_EDITOR_DECISION',			0x30000003);

// Reviewer events					0x40000000
define('MONOGRAPH_LOG_REVIEW_ASSIGN',			0x40000001);

define('MONOGRAPH_LOG_REVIEW_ACCEPT',			0x40000006);
define('MONOGRAPH_LOG_REVIEW_DECLINE',			0x40000007);

define('MONOGRAPH_LOG_REVIEW_SET_DUE_DATE',		0x40000011);

define('MONOGRAPH_LOG_REVIEW_CLEAR',			0x40000014);


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

	/**
	 * Return locale message key describing event type.
	 * @return string
	 */
	function getEventTitle() {
		switch ($this->getData('eventType')) {
			// General events
			case MONOGRAPH_LOG_MONOGRAPH_SUBMIT:
				return 'submission.event.general.monographSubmitted';
			case MONOGRAPH_LOG_METADATA_UPDATE:
				return 'submission.event.general.metadataUpdated';
			case MONOGRAPH_LOG_MONOGRAPH_PUBLISH:
				return 'submission.event.general.monographPublished';

			// Editor events
			case MONOGRAPH_LOG_EDITOR_DECISION:
				return 'submission.event.editor.editorDecision';

			// Reviewer events
			case MONOGRAPH_LOG_REVIEW_ASSIGN:
				return 'submission.event.reviewer.reviewerAssigned';
			case MONOGRAPH_LOG_REVIEW_ACCEPT:
				return 'submission.event.reviewer.reviewAccepted';
			case MONOGRAPH_LOG_REVIEW_DECLINE:
				return 'submission.event.reviewer.reviewDeclined';
			case MONOGRAPH_LOG_REVIEW_SET_DUE_DATE:
				return 'submission.event.reviewer.reviewDueDate';

			default:
				return parent::getEventTitle();
		}
	}
}

?>
