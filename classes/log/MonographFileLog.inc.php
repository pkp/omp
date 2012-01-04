<?php

/**
 * @file classes/log/MonographFileLog.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileLog
 * @ingroup log
 *
 * @brief Static class for adding / accessing monograph file log entries.
 */

import('classes.log.OmpLog');

class MonographFileLog extends OmpLog {
	/**
	 * Add a new file event log entry with the specified parameters
	 * @param $request object
	 * @param $monographFile object
	 * @param $eventType int
	 * @param $messageKey string
	 * @param $params array optional
	 * @return object MonographLogEntry iff the event was logged
	 */
	function logEvent(&$request, &$monographFile, $eventType, $messageKey, $params = array()) {
		// Create a new entry object
		$monographFileEventLogDao =& DAORegistry::getDAO('MonographFileEventLogDAO');
		$entry = $monographFileEventLogDao->newDataObject();

		// Set implicit parts of the log entry
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setIPAddress($request->getRemoteAddr());

		$user =& $request->getUser();
		if ($user) $entry->setUserId($user->getId());

		$entry->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
		$entry->setAssocId($monographFile->getFileId());

		// Set explicit parts of the log entry
		$entry->setEventType($eventType);
		$entry->setMessage($messageKey);
		$entry->setParams($params);
		$entry->setIsTranslated(0); // Legacy for other apps. All messages use locale keys.

		// Insert the resulting object
		$monographFileEventLogDao->insertObject($entry);
		return $entry;
	}
}

?>
