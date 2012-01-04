<?php

/**
 * @file classes/log/MonographLog.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographLog
 * @ingroup log
 *
 * @brief Static class for adding / accessing monograph log entries.
 */

import('classes.log.OmpLog');

class MonographLog extends OmpLog {
	/**
	 * Add a new event log entry with the specified parameters
	 * @param $request object
	 * @param $monograph object
	 * @param $eventType int
	 * @param $messageKey string
	 * @param $params array optional
	 * @return object MonographLogEntry iff the event was logged
	 */
	function logEvent(&$request, &$monograph, $eventType, $messageKey, $params = array()) {
		// Create a new entry object
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$entry = $monographEventLogDao->newDataObject();

		// Set implicit parts of the log entry
		$entry->setDateLogged(Core::getCurrentDate());
		$entry->setIPAddress($request->getRemoteAddr());

		$user =& $request->getUser();
		if ($user) $entry->setUserId($user->getId());

		$entry->setAssocType(ASSOC_TYPE_MONOGRAPH);
		$entry->setAssocId($monograph->getId());

		// Set explicit parts of the log entry
		$entry->setEventType($eventType);
		$entry->setMessage($messageKey);
		$entry->setParams($params);
		$entry->setIsTranslated(0); // Legacy for other apps. All messages use locale keys.

		// Insert the resulting object
		$monographEventLogDao->insertObject($entry);
		return $entry;
	}
}

?>
