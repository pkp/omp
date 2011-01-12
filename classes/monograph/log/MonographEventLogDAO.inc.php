<?php

/**
 * @file classes/monograph/log/MonographEventLogDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEventLogDAO
 * @ingroup monograph_log
 * @see MonographEventLogEntry
 *
 * @brief Class for inserting/accessing monograph history log entries.
 */



import ('classes.monograph.log.MonographEventLogEntry');

class MonographEventLogDAO extends DAO {
	/**
	 * Retrieve a log entry by ID.
	 * @param $logId int
	 * @param $monographId int optional
	 * @return MonographEventLogEntry
	 */
	function &getLogEntry($logId, $monographId = null) {
		if (isset($monographId)) {
			$result =& $this->retrieve(
				'SELECT * FROM monograph_event_log WHERE log_id = ? AND monograph_id = ?',
				array($logId, $monographId)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT * FROM monograph_event_log WHERE log_id = ?', $logId
			);
		}

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnLogEntryFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all log entries for a monograph.
	 * @param $monographId int
	 * @return DAOResultFactory containing matching MonographEventLogEntry MonographEventLogEntry ordered by sequence
	 */
	function &getMonographLogEntries($monographId, $rangeInfo = null) {
		$returner =& $this->getMonographLogEntriesByAssoc($monographId, null, null, $rangeInfo);
		return $returner;
	}

	/**
	 * Retrieve all log entries for a monograph matching the specified association.
	 * @param $monographId int
	 * @param $assocType int
	 * @param $assocId int
	 * @param $limit int limit the number of entries retrieved (default false)
	 * @param $recentFirst boolean order with most recent entries first (default true)
	 * @return DAOResultFactory containing matching MonographEventLogEntry ordered by sequence
	 */
	function &getMonographLogEntriesByAssoc($monographId, $assocType = null, $assocId = null, $rangeInfo = null) {
		$params = array($monographId);
		if (isset($assocType)) {
			array_push($params, $assocType);
			if (isset($assocId)) {
				array_push($params, $assocId);
			}
		}

		$result =& $this->retrieveRange(
			'SELECT * FROM monograph_event_log WHERE monograph_id = ?' . (isset($assocType) ? ' AND assoc_type = ?' . (isset($assocId) ? ' AND assoc_id = ?' : '') : '') . ' ORDER BY log_id DESC',
			$params, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnLogEntryFromRow');
		return $returner;
	}

	/**
	 * Internal function to return a MonographEventLogEntry object from a row.
	 * @param $row array
	 * @return MonographEventLogEntry
	 */
	function &_returnLogEntryFromRow(&$row) {
		$entry = new MonographEventLogEntry();
		$entry->setLogId($row['log_id']);
		$entry->setMonographId($row['monograph_id']);
		$entry->setUserId($row['user_id']);
		$entry->setDateLogged($this->datetimeFromDB($row['date_logged']));
		$entry->setIPAddress($row['ip_address']);
		$entry->setLogLevel($row['log_level']);
		$entry->setEventType($row['event_type']);
		$entry->setAssocType($row['assoc_type']);
		$entry->setAssocId($row['assoc_id']);
		$entry->setMessage($row['message']);

		HookRegistry::call('MonographEventLogDAO::_returnLogEntryFromRow', array(&$entry, &$row));

		return $entry;
	}

	/**
	 * Insert a new log entry.
	 * @param $entry MonographEventLogEntry
	 */	
	function insertLogEntry(&$entry) {
		if ($entry->getDateLogged() == null) {
			$entry->setDateLogged(Core::getCurrentDate());
		}
		if ($entry->getIPAddress() == null) {
			$entry->setIPAddress(Request::getRemoteAddr());
		}
		$this->update(
			sprintf('INSERT INTO monograph_event_log
				(monograph_id, user_id, date_logged, ip_address, log_level, event_type, assoc_type, assoc_id, message)
				VALUES
				(?, ?, %s, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($entry->getDateLogged())),
			array(
				$entry->getMonographId(),
				$entry->getUserId(),
				$entry->getIPAddress(),
				$entry->getLogLevel(),
				$entry->getEventType(),
				$entry->getAssocType(),
				$entry->getAssocId(),
				$entry->getMessage()
			)
		);

		$entry->setLogId($this->getInsertLogId());
		return $entry->getLogId();
	}

	/**
	 * Delete a single log entry for a monograph.
	 * @param $logId int
	 * @param $monographId int optional
	 */
	function deleteLogEntry($logId, $monographId = null) {
		if (isset($monographId)) {
			return $this->update(
				'DELETE FROM monograph_event_log WHERE log_id = ? AND monograph_id = ?',
				array($logId, $monographId)
			);

		} else {
			return $this->update(
				'DELETE FROM monograph_event_log WHERE log_id = ?', $logId
			);
		}
	}

	/**
	 * Delete all log entries for a monograph.
	 * @param $monographId int
	 */
	function deleteMonographLogEntries($monographId) {
		return $this->update(
			'DELETE FROM monograph_event_log WHERE monograph_id = ?', $monographId
		);
	}

	/**
	 * Transfer all monograph log entries to another user.
	 * @param $monographId int
	 */
	function transferMonographLogEntries($oldUserId, $newUserId) {
		return $this->update(
			'UPDATE monograph_event_log SET user_id = ? WHERE user_id = ?',
			array($newUserId, $oldUserId)
		);
	}

	/**
	 * Get the ID of the last inserted log entry.
	 * @return int
	 */
	function getInsertLogId() {
		return $this->getInsertId('monograph_event_log', 'log_id');
	}
}

?>
