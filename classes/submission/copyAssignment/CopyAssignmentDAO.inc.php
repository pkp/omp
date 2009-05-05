<?php

/**
 * @file classes/submission/copyAssignment/CopyAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyAssignmentDAO
 * @ingroup submission
 * @see CopyAssignment
 *
 * @brief Operations for retrieving and modifying CopyAssignment objects.
 */

// $Id$


import('submission.copyAssignment.CopyAssignment');

class CopyAssignmentDAO extends DAO {
	var $monographFileDao;

	/**
	 * Constructor.
	 */
	function CopyAssignmentDAO() {
		parent::DAO();
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
	}

	/**
	 * Retrieve a copyed assignment by monograph ID.
	 * @param $copyedId int
	 * @return copyAssignment
	 */
	function &getById($copyedId) {
		$result =& $this->retrieve(
			'SELECT c.*, u.first_name, u.last_name FROM copyed_assignments c LEFT JOIN users u ON (c.copyeditor_id = u.user_id) WHERE c.copyed_id = ?',
			$copyedId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a copy assignment by monograph ID.
	 * @param $monographId int
	 * @return CopyAssignment
	 */
	function &getByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT c.*, a.copyedit_file_id, u.first_name, u.last_name FROM copyed_assignments c LEFT JOIN monographs a ON (c.monograph_id = a.monograph_id) LEFT JOIN users u ON (c.copyeditor_id = u.user_id) WHERE c.monograph_id = ?',
			$monographId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return AcquisitionsEditorSubmission
	 */
	function newDataObject() {
		return new CopyAssignment();
	}

	/**
	 * Internal function to return a CopyAssignment object from a row.
	 * @param $row array
	 * @return CopyAssignment
	 */
	function &_fromRow(&$row) {
		$copyAssignment = $this->newDataObject();

		// Copyedit Assignment
		$copyAssignment->setCopyedId($row['copyed_id']);
		$copyAssignment->setMonographId($row['monograph_id']);
		$copyAssignment->setCopyeditorId($row['copyeditor_id']);
		$copyAssignment->setCopyeditorFullName($row['first_name'].' '.$row['last_name']);
		$copyAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
		$copyAssignment->setDateUnderway($this->datetimeFromDB($row['date_underway']));
		$copyAssignment->setDateCompleted($this->datetimeFromDB($row['date_completed']));
		$copyAssignment->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
		$copyAssignment->setDateAuthorNotified($this->datetimeFromDB($row['date_author_notified']));
		$copyAssignment->setDateAuthorUnderway($this->datetimeFromDB($row['date_author_underway']));
		$copyAssignment->setDateAuthorCompleted($this->datetimeFromDB($row['date_author_completed']));
		$copyAssignment->setDateAuthorAcknowledged($this->datetimeFromDB($row['date_author_acknowledged']));
		$copyAssignment->setDateFinalNotified($this->datetimeFromDB($row['date_final_notified']));
		$copyAssignment->setDateFinalUnderway($this->datetimeFromDB($row['date_final_underway']));
		$copyAssignment->setDateFinalCompleted($this->datetimeFromDB($row['date_final_completed']));
		$copyAssignment->setDateFinalAcknowledged($this->datetimeFromDB($row['date_final_acknowledged']));
		$copyAssignment->setInitialRevision($row['initial_revision']);
		$copyAssignment->setEditorAuthorRevision($row['editor_author_revision']);
		$copyAssignment->setFinalRevision($row['final_revision']);

		// Files

		// Initial Copyedit File
		if ($row['initial_revision'] != null) {
			$copyAssignment->setInitialCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['initial_revision']));
		}

		// Editor / Author Copyedit File
		if ($row['editor_author_revision'] != null) {
			$copyAssignment->setEditorAuthorCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['editor_author_revision']));
		}

		// Final Copyedit File
		if ($row['final_revision'] != null) {
			$copyAssignment->setFinalCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['final_revision']));
		}

		HookRegistry::call('CopyAssignmentDAO::_returnCopyAssignmentFromRow', array(&$copyAssignment, &$row));

		return $copyAssignment;
	}

	/**
	 * Delete copyediting assignments by monograph.
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		return $this->update(
			'DELETE FROM copyed_assignments WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Get the ID of the last inserted copyeditor assignment.
	 * @return int
	 */
	function getInsertCopyedId() {
		return $this->getInsertId('copyed_assignments', 'copyed_id');
	}
}

?>
