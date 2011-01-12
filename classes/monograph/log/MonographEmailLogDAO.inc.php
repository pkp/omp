<?php

/**
 * @file classes/monograph/log/MonographEmailLogDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEmailLogDAO
 * @ingroup monograph_log
 * @see MonographEmailLogEntry, MonographLog
 *
 * @brief Class for inserting/accessing monograph email log entries.
 */



import ('classes.monograph.log.MonographEmailLogEntry');

class MonographEmailLogDAO extends DAO {
	/**
	 * Retrieve a log entry by ID.
	 * @param $logId int
	 * @param $monographId int optional
	 * @return MonographEmailLogEntry
	 */
	function &getLogEntry($logId, $monographId = null) {
		if (isset($monographId)) {
			$result =& $this->retrieve(
				'SELECT * FROM monograph_email_log WHERE log_id = ? AND monograph_id = ?',
				array($logId, $monographId)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT * FROM monograph_email_log WHERE log_id = ?', $logId
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
	 * @return DAOResultFactory containing matching MonographEmailLogEntry ordered by sequence
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
			'SELECT * FROM monograph_email_log WHERE monograph_id = ?' . (isset($assocType) ? ' AND assoc_type = ?' . (isset($assocId) ? ' AND assoc_id = ?' : '') : '') . ' ORDER BY log_id DESC',
			$params, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnLogEntryFromRow');
		return $returner;
	}

	/**
	 * Internal function to return a MonographEmailLogEntry object from a row.
	 * @param $row array
	 * @return MonographEmailLogEntry
	 */
	function &_returnLogEntryFromRow(&$row) {
		$entry = new MonographEmailLogEntry();
		$entry->setLogId($row['log_id']);
		$entry->setMonographId($row['monograph_id']);
		$entry->setSenderId($row['sender_id']);
		$entry->setDateSent($this->datetimeFromDB($row['date_sent']));
		$entry->setIPAddress($row['ip_address']);
		$entry->setEventType($row['event_type']);
		$entry->setAssocType($row['assoc_type']);
		$entry->setAssocId($row['assoc_id']);
		$entry->setFrom($row['from_address']);
		$entry->setRecipients($row['recipients']);
		$entry->setCcs($row['cc_recipients']);
		$entry->setBccs($row['bcc_recipients']);
		$entry->setSubject($row['subject']);
		$entry->setBody($row['body']);

		HookRegistry::call('MonographEmailLogDAO::_returnLogEntryFromRow', array(&$entry, &$row));

		return $entry;
	}

	/**
	 * Insert a new log entry.
	 * @param $entry MonographEmailLogEntry
	 */	
	function insertLogEntry(&$entry) {
		if ($entry->getDateSent() == null) {
			$entry->setDateSent(Core::getCurrentDate());
		}
		if ($entry->getIPAddress() == null) {
			$entry->setIPAddress(Request::getRemoteAddr());
		}
		$this->update(
			sprintf('INSERT INTO monograph_email_log
				(monograph_id, sender_id, date_sent, ip_address, event_type, assoc_type, assoc_id, from_address, recipients, cc_recipients, bcc_recipients, subject, body)
				VALUES
				(?, ?, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($entry->getDateSent())),
			array(
				$entry->getMonographId(),
				$entry->getSenderId(),
				$entry->getIPAddress(),
				$entry->getEventType(),
				$entry->getAssocType(),
				$entry->getAssocId(),
				$entry->getFrom(),
				$entry->getRecipients(),
				$entry->getCcs(),
				$entry->getBccs(),
				$entry->getSubject(),
				$entry->getBody()
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
				'DELETE FROM monograph_email_log WHERE log_id = ? AND monograph_id = ?',
				array($logId, $monographId)
			);

		} else {
			return $this->update(
				'DELETE FROM monograph_email_log WHERE log_id = ?', $logId
			);
		}
	}

	/**
	 * Delete all log entries for a monograph.
	 * @param $monographId int
	 */
	function deleteMonographLogEntries($monographId) {
		return $this->update(
			'DELETE FROM monograph_email_log WHERE monograph_id = ?', $monographId
		);
	}

	/**
	 * Transfer all monograph log entries to another user.
	 * @param $monographId int
	 */
	function transferMonographLogEntries($oldUserId, $newUserId) {
		return $this->update(
			'UPDATE monograph_email_log SET sender_id = ? WHERE sender_id = ?',
			array($newUserId, $oldUserId)
		);
	}

	/**
	 * Get the ID of the last inserted log entry.
	 * @return int
	 */
	function getInsertLogId() {
		return $this->getInsertId('monograph_email_log', 'log_id');
	}
}

?>
