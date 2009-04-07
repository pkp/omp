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
	function &getById($assignmentId) {
		$result =& $this->retrieve(
			'SELECT l.*, u.first_name, u.last_name, u.email
				FROM designer_assignments l
				LEFT JOIN users u ON (l.designer_id = u.user_id)
				WHERE l.assignment_id = ?',
			$assignmentId
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
	 * Retrieve the IDs of the designers for an monograph.
	 * @param $monographId int
	 * @return array (int)
	 */
	function &getDesignerIdsByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT designer_id FROM designer_assignments WHERE monograph_id = ?',
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
	function &getByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT l.*, u.first_name, u.last_name, u.email
				FROM designer_assignments l
				LEFT JOIN users u ON (l.designer_id = u.user_id)
				WHERE l.monograph_id = ?',
			$monographId
		);

		$returner = array();
		while (!$result->EOF) {
			$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ControlledVocabEntry
	 */
	function newDataObject() {
		return new LayoutAssignment();
	}

	/**
	 * Internal function to return a LayoutAssignment object from a row.
	 * @param $row array
	 * @return LayoutAssignment
	 */
	function &_fromRow(&$row) {
		$layoutAssignment = $this->newDataObject();
		$layoutAssignment->setId($row['assignment_id']);
		$layoutAssignment->setMonographId($row['monograph_id']);
		$layoutAssignment->setDesignerId($row['designer_id']);
		$layoutAssignment->setDesignerFullName($row['first_name'].' '.$row['last_name']);
		$layoutAssignment->setDesignerEmail($row['email']);
		$layoutAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
		$layoutAssignment->setDateUnderway($this->datetimeFromDB($row['date_underway']));
		$layoutAssignment->setDateCompleted($this->datetimeFromDB($row['date_completed']));
		$layoutAssignment->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));

		/*$layoutAssignment->setLayoutFileId($row['layout_file_id']);
		if ($row['layout_file_id'] && $row['layout_file_id']) {
			$layoutAssignment->setLayoutFile($this->monographFileDao->getMonographFile($row['layout_file_id']));
		}*/

		HookRegistry::call('LayoutAssignmentDAO::_returnLayoutAssignmentFromRow', array(&$layoutAssignment, &$row));

		return $layoutAssignment;
	}

	/**
	 * Insert a new layout assignment.
	 * @param $layoutAssignment LayoutAssignment
	 * @return int
	 */	
	function insertLayoutAssignment(&$layoutAssignment) {
		$this->update(
			sprintf('INSERT INTO designer_assignments
				(monograph_id, designer_id, date_notified, date_underway, date_completed, date_acknowledged)
				VALUES
				(?, ?, %s, %s, %s, %s)',
				$this->datetimeToDB($layoutAssignment->getDateNotified()), $this->datetimeToDB($layoutAssignment->getDateUnderway()), $this->datetimeToDB($layoutAssignment->getDateCompleted()), $this->datetimeToDB($layoutAssignment->getDateAcknowledged())),
			array(
				$layoutAssignment->getMonographId(),
				$layoutAssignment->getDesignerId()
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
			sprintf('UPDATE designer_assignments
				SET	monograph_id = ?,
					designer_id = ?,
					date_notified = %s,
					date_underway = %s,
					date_completed = %s,
					date_acknowledged = %s
				WHERE assignment_id = ?',
				$this->datetimeToDB($layoutAssignment->getDateNotified()), $this->datetimeToDB($layoutAssignment->getDateUnderway()), $this->datetimeToDB($layoutAssignment->getDateCompleted()), $this->datetimeToDB($layoutAssignment->getDateAcknowledged())),
			array(
				$layoutAssignment->getMonographId(),
				$layoutAssignment->getDesignerId(),
				$layoutAssignment->getId()
			)
		);
	}

	/**
	 * Delete layout assignment.
	 * @param $layoutId int
	 */
	function deleteById($layoutId) {
		return $this->update(
			'DELETE FROM designer_assignments WHERE assignment_id = ?',
			$layoutId
		);
	}

	/**
	 * Delete layout assignments by monograph.
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		return $this->update(
			'DELETE FROM designer_assignments WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Get the ID of the last inserted layout assignment.
	 * @return int
	 */
	function getInsertLayoutId() {
		return $this->getInsertId('designer_assignments', 'assignment_id');
	}
}

?>