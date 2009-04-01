<?php

/**
 * @file classes/submission/layoutAssignment/LayoutAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutAssignmentDAO
 * @ingroup submission_layoutAssignment
 * @see LayoutAssignment
 *
 * @brief DAO class for layout assignments.
 */

// $Id$


import('submission.layoutAssignment.LayoutAssignment');

class LayoutAssignmentDAO extends DAO {
	var $monographFileDao;

	/**
	 * Constructor.
	 */
	function LayoutAssignmentDAO() {
		parent::DAO();
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
	}

	/**
	 * Retrieve a layout assignment by assignment ID.
	 * @param $assignmentId int
	 * @return LayoutAssignment or null
	 */
	function &getLayoutAssignmentById($assignmentId) {
		$result =& $this->retrieve(
			'SELECT l.*, u.first_name, u.last_name, u.email
				FROM layout_assignments l
				LEFT JOIN users u ON (l.designer_id = u.user_id)
				WHERE l.assignment_id = ?',
			$assignmentId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnLayoutAssignmentFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve the IDs of the designers for an monograph.
	 * @param $monographId int
	 * @return array (int)
	 */
	function &getDesignerIdsByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT designer_id FROM layout_assignments WHERE monograph_id = ?',
			$monographId
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[] = $row['designer_id'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve the layout assignments for an monograph.
	 * @param $monographId int
	 * @return array (LayoutAssignment)
	 */
	function &getLayoutAssignmentsByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT l.*, u.first_name, u.last_name, u.email
				FROM layout_assignments l
				LEFT JOIN users u ON (l.designer_id = u.user_id)
				WHERE l.monograph_id = ?',
			$monographId
		);

		$returner = array();
		while (!$result->EOF) {
			$returner[] =& $this->_returnLayoutAssignmentFromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a layout assignment object from a row.
	 * @param $row array
	 * @return LayoutAssignment
	 */
	function &_returnLayoutAssignmentFromRow(&$row) {
		$layoutAssignment = new LayoutAssignment();
		$layoutAssignment->setId($row['assignment_id']);
		$layoutAssignment->setMonographId($row['monograph_id']);
		$layoutAssignment->setDesignerId($row['designer_id']);
		$layoutAssignment->setDesignerFullName($row['first_name'].' '.$row['last_name']);
		$layoutAssignment->setDesignerEmail($row['email']);
		$layoutAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
		$layoutAssignment->setDateUnderway($this->datetimeFromDB($row['date_underway']));
		$layoutAssignment->setDateCompleted($this->datetimeFromDB($row['date_completed']));
		$layoutAssignment->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
		$layoutAssignment->setLayoutFileId($row['layout_file_id']);

		if ($row['layout_file_id'] && $row['layout_file_id']) {
			$layoutAssignment->setLayoutFile($this->monographFileDao->getMonographFile($row['layout_file_id']));
		}

		HookRegistry::call('LayoutAssignmentDAO::_returnLayoutAssignmentFromRow', array(&$layoutAssignment, &$row));

		return $layoutAssignment;
	}

	/**
	 * Insert a new layout assignment.
	 * @param $layoutAssignment LayoutAssignment
	 */	
	function insertLayoutAssignment(&$layoutAssignment) {
		$this->update(
			sprintf('INSERT INTO layout_assignments
				(monograph_id, designer_id, date_notified, date_underway, date_completed, date_acknowledged, layout_file_id)
				VALUES
				(?, ?, %s, %s, %s, %s, ?)',
				$this->datetimeToDB($layoutAssignment->getDateNotified()), $this->datetimeToDB($layoutAssignment->getDateUnderway()), $this->datetimeToDB($layoutAssignment->getDateCompleted()), $this->datetimeToDB($layoutAssignment->getDateAcknowledged())),
			array(
				$layoutAssignment->getMonographId(),
				$layoutAssignment->getDesignerId(),
				$layoutAssignment->getLayoutFileId()
			)
		);

		$layoutAssignment->setId($this->getInsertLayoutId());
		return $layoutAssignment->getId();
	}

	/**
	 * Update an layout assignment.
	 * @param $layoutAssignment LayoutAssignment
	 */
	function updateLayoutAssignment(&$layoutAssignment) {
		return $this->update(
			sprintf('UPDATE layout_assignments
				SET	monograph_id = ?,
					designer_id = ?,
					date_notified = %s,
					date_underway = %s,
					date_completed = %s,
					date_acknowledged = %s,
					layout_file_id = ?
				WHERE assignment_id = ?',
				$this->datetimeToDB($layoutAssignment->getDateNotified()), $this->datetimeToDB($layoutAssignment->getDateUnderway()), $this->datetimeToDB($layoutAssignment->getDateCompleted()), $this->datetimeToDB($layoutAssignment->getDateAcknowledged())),
			array(
				$layoutAssignment->getMonographId(),
				$layoutAssignment->getDesignerId(),
				$layoutAssignment->getLayoutFileId(),
				$layoutAssignment->getId()
			)
		);
	}

	/**
	 * Delete layout assignment.
	 * @param $layoutId int
	 */
	function deleteLayoutAssignmentById($layoutId) {
		return $this->update(
			'DELETE FROM layout_assignments WHERE assignment_id = ?',
			$layoutId
		);
	}

	/**
	 * Delete layout assignments by monograph.
	 * @param $monographId int
	 */
	function deleteLayoutAssignmentsByMonograph($monographId) {
		return $this->update(
			'DELETE FROM layout_assignments WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Get the ID of the last inserted layout assignment.
	 * @return int
	 */
	function getInsertLayoutId() {
		return $this->getInsertId('layout_assignments', 'assignment_id');
	}
}

?>