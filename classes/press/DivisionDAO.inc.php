<?php

/**
 * @file classes/press/DivisionDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DivisionDAO
 * @ingroup press
 * @see Division
 *
 * @brief Operations for retrieving and modifying Division objects.
 */



import ('classes.press.Division');

class DivisionDAO extends DAO {
	/**
	 * Retrieve an division by ID.
	 * @param $divisionId int
	 * @return Division
	 */
	function &getById($divisionId, $pressId = null) {
		$sql = 'SELECT * FROM divisions WHERE division_id = ?';
		$params = array($divisionId);

		if ($pressId !== null) {
			$sql .= ' AND press_id = ?';
			$params[] = $pressId;
		}
		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an division by title.
	 * @param $divisionTitle string
	 * @return Division
	 */
	function &getByTitle($divisionTitle, $pressId, $locale = null) {
		$sql = 'SELECT a.* FROM divisions a, divisions_settings l WHERE l.division_id = a.division_id AND l.setting_name = ? AND l.setting_value = ? AND a.press_id = ?';
		$params = array('title', $divisionTitle, $pressId);
		if ($locale !== null) {
			$sql .= ' AND l.locale = ?';
			$params[] = $locale;
		}

		$result =& $this->retrieve($sql, $params);

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
	 * @return Division
	 */
	function newDataObject() {
		return new Division();
	}

	/**
	 * Internal function to return an Division object from a row.
	 * @param $row array
	 * @return Division
	 */
	function _fromRow(&$row) {
		$division = $this->newDataObject();

		$division->setId($row['division_id']);
		$division->setPressId($row['press_id']);

		$this->getDataObjectSettings('divisions_settings', 'division_id', $row['division_id'], $division);

		HookRegistry::call('DivisionDAO::_fromRow', array(&$division, &$row));

		return $division;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Update the localized fields for this table
	 * @param $division object
	 */
	function updateLocaleFields(&$division) {
		$this->updateDataObjectSettings('divisions_settings', $division, array(
			'division_id' => $division->getId()
		));
	}

	/**
	 * Insert a new division.
	 * @param $division Division
	 */
	function insertObject(&$division) {
		$this->update(
			'INSERT INTO divisions
				(press_id)
				VALUES
				(?)',
			array(
				$division->getPressId(),
			)
		);

		$division->setId($this->getInsertDivisionId());
		$this->updateLocaleFields($division);
		return $division->getId();
	}

	/**
	 * Update an existing division.
	 * @param $division Division
	 */
	function updateObject($division) {
		$returner = $this->update(
			'UPDATE divisions
				SET
					press_id = ?,
				WHERE division_id = ?',
			array(
				$division->getPressId(),
				$division->getId()
			)
		);
		$this->updateLocaleFields($division);
		return $returner;
	}

	/**
	 * Delete an division.
	 * @param $division Division
	 */
	function deleteObject(&$division) {
		return $this->deleteById($division->getId(), $division->getPressId());
	}

	/**
	 * Delete an division by ID.
	 * @param $divisionId int
	 * @param $pressId int optional
	 */
	function deleteById($divisionId, $pressId = null) {
		if (isset($divisionId) && !$this->divisionExists($divisionId, $pressId)) return false;
		$this->update('DELETE FROM divisions_settings WHERE division_id = ?', array($divisionId));
		return $this->update('DELETE FROM divisions WHERE division_id = ?', array($divisionId));
	}

	/**
	 * Delete division by press ID
	 * NOTE: This does not delete dependent entries. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$divisions =& $this->getByPressId($pressId);
		while ($division =& $divisions->next()) {
			$this->deleteObject($division);
			unset($division);
		}
	}

	/**
	 * Retrieve all divisions for a press.
	 * @return DAOResultFactory containing Division ordered by sequence
	 */
	function &getByPressId($pressId, $rangeInfo = null) {
		$sql = 'SELECT * FROM divisions WHERE press_id = ?';
		$params = array($pressId);

		$result =& $this->retrieveRange($sql, $params, $rangeInfo);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Check if a division exists with the specified ID.
	 * @param $sdivisionId int
	 * @param $pressId int
	 * @return boolean
	 */
	function divisionExists($divisionId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM divisions WHERE division_id = ? AND press_id = ?',
			array($divisionId, $pressId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the ID of the last inserted division.
	 * @return int
	 */
	function getInsertDivisionId() {
		return $this->getInsertId('divisions', 'division_id');
	}


	function updateSetting($divisionId, $name, $value) {
		$this->update('DELETE FROM divisions_settings WHERE divisions_id = ? AND setting_name = ?',
				array($divisionId, $name)
			);
		$this->update('INSERT INTO divisions_settings
			(divisions_id, setting_name, setting_value, setting_type)
			VALUES (?, ?, ?, ?)',
			array(
				$divisionId, $name, $value, 'string'
			)
		);
	}

	function getSetting($divisionId, $name) {
		$result =& $this->retrieve(
			'SELECT setting_value FROM divisions_settings WHERE division_id = ? AND setting_name = ?',
			array($divisionId, $name)
		);

		if (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$value = $row['setting_value'];
		} else $value = null;

		$result->Close();
		unset($result);

		return $value;
	}

}

?>
