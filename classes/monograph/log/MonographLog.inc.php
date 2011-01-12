<?php

/**
 * @defgroup monograph_log
 */

/**
 * @file classes/monograph/log/MonographLog.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographLog
 * @ingroup monograph_log
 *
 * @brief Static class for adding / accessing monograph log entries.
 */



class MonographLog {

	/**
	 * Add an event log entry to this monograph.
	 * @param $monographId int
	 * @param $entry MonographEventLogEntry
	 */
	function logEventEntry($monographId, &$entry) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$pressId = $monographDao->getMonographPressId($monographId);

		if (!$pressId) {
			// Invalid monograph
			return false;
		}

		// Add the entry
		$entry->setMonographId($monographId);

		if ($entry->getUserId() == null) {
			$user =& Request::getUser();
			$entry->setUserId($user == null ? 0 : $user->getId());
		}

		$logDao =& DAORegistry::getDAO('MonographEventLogDAO');
		return $logDao->insertLogEntry($entry);
	}

	/**
	 * Add a new event log entry with the specified parameters, at the default log level
	 * @param $monographId int
	 * @param $eventType int
	 * @param $assocType int
	 * @param $assocId int
	 * @param $messageKey string
	 * @param $messageParams array
	 */
	function logEvent($monographId, $eventType, $assocType = 0, $assocId = 0, $messageKey = null, $messageParams = array()) {
		return MonographLog::logEventLevel($monographId, MONOGRAPH_LOG_LEVEL_NOTICE, $eventType, $assocType, $assocId, $messageKey, $messageParams);
	}

	/**
	 * Add a new event log entry with the specified parameters, including log level.
	 * @param $monographId int
	 * @param $logLevel char
	 * @param $eventType int
	 * @param $assocType int
	 * @param $assocId int
	 * @param $messageKey string
	 * @param $messageParams array
	 */
	function logEventLevel($monographId, $logLevel, $eventType, $assocType = 0, $assocId = 0, $messageKey = null, $messageParams = array()) {
		$entry = new MonographEventLogEntry();
		$entry->setLogLevel($logLevel);
		$entry->setEventType($eventType);
		$entry->setAssocType($assocType);
		$entry->setAssocId($assocId);

		if (isset($messageKey)) {
			$entry->setLogMessage($messageKey, $messageParams);
		}

		return MonographLog::logEventEntry($monographId, $entry);
	}

	/**
	 * Get all event log entries for a monograph.
	 * @param $monographId int
	 * @return array MonographEventLogEntry
	 */
	function &getEventLogEntries($monographId, $rangeInfo = null) {
		$logDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$returner =& $logDao->getMonographLogEntries($monographId, $rangeInfo);
		return $returner;
	}

	/**
	 * Add an email log entry to this monograph.
	 * @param $monographId int
	 * @param $entry MonographEmailLogEntry
	 */
	function logEmailEntry($monographId, &$entry) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$pressId = $monographDao->getMonographPressId($monographId);

		if (!$pressId) {
			// Invalid monograph
			return false;
		}

		// Add the entry
		$entry->setMonographId($monographId);

		if ($entry->getSenderId() == null) {
			$user =& Request::getUser();
			$entry->setSenderId($user == null ? 0 : $user->getId());
		}

		$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		return $logDao->insertLogEntry($entry);
	}

	/**
	 * Get all email log entries for a monograph.
	 * @param $monographId int
	 * @return array MonographEmailLogEntry
	 */
	function &getEmailLogEntries($monographId, $rangeInfo = null) {
		$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$result =& $logDao->getMonographLogEntries($monographId, $rangeInfo);
		return $result;
	}

}

?>
