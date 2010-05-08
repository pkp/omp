<?php

/**
 * @file classes/submission/productionAssignment/ProductionAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionAssignmentDAO
 * @ingroup submission_productionAssignment
 * @see ProductionAssignment
 *
 * @brief DAO class for production assignments.
 */

// $Id$


import('classes.submission.productionAssignment.ProductionAssignment');

class ProductionAssignmentDAO extends DAO {
	var $monographFileDao;

	/**
	 * Constructor.
	 */
	function ProductionAssignmentDAO() {
		parent::DAO();
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
	}

	function productionAssignmentTypeToLocaleKey($type = null) {

		$typeToKey = array(
			DESIGN_ASSIGNMENT_TYPE_LAYOUT => 'production.assignment.type.layout',
			DESIGN_ASSIGNMENT_TYPE_COVER => 'production.assignment.type.cover',
			DESIGN_ASSIGNMENT_TYPE_VISUALS => 'production.assignment.type.visuals',
			DESIGN_ASSIGNMENT_TYPE_MEDIA => 'production.assignment.type.media'
		);

		if ($type == null) {
			return $typeToKey;
		} else {
			return isset($typeToKey[$type]) ? $typeToKey[$type] : null;
		}
	}

	/**
	 * Retrieve a Production Assignment by Id.
	 * @param $assignmentId int
	 * @return ProductionAssignment object
	 */
	function &getById($assignmentId) {
		$result =& $this->retrieve(
			'SELECT *
			FROM production_assignments da
			WHERE da.assignment_id = ?',
			(int) $assignmentId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve the production assignments for a monograph.
	 * @param $monographId int
	 * @param $userId int optional - filter by associated user
	 * @return array (ProductionAssignment)
	 */
	function &getByMonographId($monographId, $userId = null) {
		$sqlExtra = '';
		$sqlParams = array();

		if ($userId !== null) {
			$sqlExtra .= 'LEFT JOIN signoffs s ON (s.assoc_type = ? AND s.assoc_id = da.assignment_id AND s.date_notified IS NOT NULL)';
			$sqlParams[] = ASSOC_TYPE_PRODUCTION_ASSIGNMENT;
			$sqlParams[] = $userId;
		}

		$sqlParams[] = $monographId;

		$result =& $this->retrieve(
			'SELECT *
			FROM production_assignments da '. $sqlExtra .'
			WHERE '. ($userId != null ? 's.user_id = ? AND ' : '') .'da.monograph_id = ?',
			$sqlParams
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
	 * Retrieve the files associated with a production assignment.
	 * @param $monographId int
	 * @param $assignmentId int
	 * @return array (File)
	 */
	function &getFilesByMonographId($monographId, $assignmentId = null) {
		$sql = 'SELECT f.*, da.assignment_id AS production_assignment_id
			FROM production_assignments da
			LEFT JOIN signoffs s ON (s.assoc_type = ? AND s.assoc_id = da.assignment_id)
			WHERE da.monograph_id = ?';
		$sqlParams = array(ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $monographId);

		if ($assignmentId !== null) {
			$sql .= ' AND da.assignment_id = ?';
			$sqlParams[] = $assignmentId;
		}

		$result =& $this->retrieve($sql, $sqlParams);

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$returner = array();
		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$signoff =& $signoffDao->_fromRow($row);
			if ($assignmentId == null) {
				$returner[$row['production_assignment_id']][$signoff->getSymbolic()] =& $signoff;
			} else {
				$returner[$signoff->getSymbolic()] =& $signoff;
			}
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve the signoffs for a production assignment.
	 * @param $monographId int
	 * @param $assignmentId int
	 * @return array (Signoff)
	 */
	function &getSignoffsByMonographId($monographId, $assignmentId = null, $hunkIt = false) {

		$sql = 'SELECT s.*, da.assignment_id AS production_assignment_id
			FROM production_assignments da
			LEFT JOIN signoffs s ON (s.assoc_type = ? AND s.assoc_id = da.assignment_id)
			WHERE da.monograph_id = ?';
		$sqlParams = array(ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $monographId);

		if ($assignmentId !== null) {
			$sql .= ' AND da.assignment_id = ?';
			$sqlParams[] = $assignmentId;
		}

		$result =& $this->retrieve($sql, $sqlParams);

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$returner = array();
		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$signoff =& $signoffDao->_fromRow($row);
			if ($hunkIt) {
				$returner[$row['production_assignment_id']][$signoff->getSymbolic()] =& $signoff;
			} else {
				$returner[$signoff->getSymbolic()] =& $signoff;
			}
			unset($signoff);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ProductAssignment
	 */
	function newDataObject() {
		return new ProductionAssignment();
	}

	/**
	 * Internal function to return a ProductionAssignment object from a row.
	 * @param $row array
	 * @return ProductionAssignment
	 */
	function &_fromRow(&$row) {
		$designAssignment = $this->newDataObject();
		$signoffs =& $this->getSignoffsByMonographId($row['monograph_id'], $row['assignment_id']);

		$designAssignment->setId($row['assignment_id']);
		$designAssignment->setMonographId($row['monograph_id']);
		$designAssignment->setType($row['type']);
		$designAssignment->setLabel($row['label']);
		$designAssignment->setSignoffs($signoffs);

		HookRegistry::call('ProductionAssignmentDAO::_fromRow', array(&$designAssignment, &$row));

		return $designAssignment;
	}

	/**
	 * Insert a new design assignment.
	 * @param $designAssignment ProductionAssignment
	 * @return int
	 */
	function insertObject(&$designAssignment) {
		$this->update(
			'INSERT INTO production_assignments
			(monograph_id, label, type)
			VALUES
			(?, ?, ?)',
			array(
				$designAssignment->getMonographId(),
				$designAssignment->getLabel(),
				$designAssignment->getType()
			)
		);

		$designAssignment->setId($this->getInsertAssignmentId());

		return $designAssignment->getId();
	}

	/**
	 * Update a design assignment.
	 * @param $designAssignment ProductionAssignment
	 */
	function updateObject(&$designAssignment) {
		return $this->update(
			'UPDATE production_assignments
			SET monograph_id = ?,
				label = ?,
				type = ?
			WHERE assignment_id = ?',
			array(
				$designAssignment->getMonographId(),
				$designAssignment->getLabel(),
				$designAssignment->getType(),
				$designAssignment->getId()
			)
		);
	}

	/**
	 * Delete production assignment.
	 * @param $assignmentId int
	 */
	function deleteById($assignmentId) {
		$monographGalleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$monographGalleyDao->deleteByAssignmentId($assignmentId);

		return $this->update(
			'DELETE FROM production_assignments WHERE assignment_id = ?',
			$assignmentId
		);
	}

	/**
	 * Delete production assignments by monograph.
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		$this->update(
			'DELETE FROM production_assignments WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Get the ID of the last inserted production assignment.
	 * @return int
	 */
	function getInsertAssignmentId() {
		return $this->getInsertId('production_assignments', 'assignment_id');
	}
}

?>