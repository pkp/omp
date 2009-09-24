<?php
/**	
 * @file classes/role/FlexibleRoleDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FlexibleRoleDAO
 * @ingroup role
 * @see FlexibleRole
 *
 * @brief Operations for retrieving and modifying FlexibleRole objects.
 */

import('role.FlexibleRole');

class FlexibleRoleDAO extends DAO
{
	/**
	 * Retrieve a flexible role by flexible role id.
	 * @param $roleId int
	 * @return FlexibleRole
	 */
	function getById($roleId){
		$result =& $this->retrieve('SELECT * FROM flexible_roles WHERE flexible_role_id = ?', $roleId);
		
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all enabled flexible roles
	 * @return array FlexibleRoles
	 */
	function &getEnabledByPressId($roleId) {
		$result =& $this->retrieve(
			'SELECT * FROM flexible_roles WHERE enabled=1 AND press_id = ? ORDER BY type', $roleId
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all flexible roles by arrangement id.
	 * @param $pressId int
	 * @param $arrangementId int
	 * @param $onlyIds bool 
	 * @return mixed array
	 */
	function &getByArrangementId($pressId, $arrangementId, $onlyIds = false) {

		$sql = 'SELECT ' . ($onlyIds ? 'fr.flexible_role_id ' : '* ') . 'FROM flexible_role_arrangements fra
			INNER JOIN flexible_roles fr ON (fr.flexible_role_id = fra.flexible_role_id)
			WHERE fr.enabled = 1 AND fr.press_id = ? AND fra.arrangement_id = ?';

		$result =& $this->retrieve($sql, array($pressId, $arrangementId));

		$returner = null;

		if ($onlyIds) {
			while (!$result->EOF) {
				$returner[] =& $result->fields['flexible_role_id'];
				$result->moveNext();
			}
		} else {
			while (!$result->EOF) {
				$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
				$result->moveNext();
			}
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get a list of field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name', 'abbrev');
	}

	/**
	 * Update the settings for this object
	 * @param $role object
	 */
	function updateLocaleFields(&$role) {
		$this->updateDataObjectSettings('flexible_role_settings', $role, array(
			'flexible_role_id' => $role->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return FlexibleRole
	 */
	function newDataObject() {
		return new FlexibleRole();
	}

	/**
	 * Internal function to return a FlexibleRole object from a row.
	 * @param $row array
	 * @return FlexibleRole
	 */
	function &_fromRow(&$row) {
		$flexibleRole = $this->newDataObject();
		$flexibleRole->setId($row['flexible_role_id']);
		$flexibleRole->setPressId($row['press_id']);
		$flexibleRole->setEnabled($row['enabled']);
		$flexibleRole->setType($row['type']);

		$this->getDataObjectSettings('flexible_role_settings', 'flexible_role_id', $row['flexible_role_id'], $flexibleRole);

		$this->setAssociatedArrangements($flexibleRole);

		return $flexibleRole;
	}  

	/**
	 * Insert a new flexible role.
	 * @param $role FlexibleRole
	 */	
	function insertObject(&$role) {
		$this->update(
			'INSERT INTO flexible_roles
				(press_id, type, enabled)
			VALUES
				(?, ?, ?)',
			array(
				$role->getPressId(),
				$role->getType(),
				$role->getEnabled() ? 1 : 0
			)
		);

		$role->setId($this->getInsertFlexibleRoleId());

		$this->updateLocaleFields($role);

		foreach ($role->getAssociatedArrangements() as $arrangement) {
			$this->update(
				'INSERT INTO flexible_role_arrangements (flexible_role_id, arrangement_id)
				VALUES (?, ?)',
				array($role->getId(), $arrangement)
			);
		}

		return $role->getId();
	}

	/**
	 * Update an existing flexible role.
	 * @param $role FlexibleRole
	 */
	function updateObject(&$role) {
		$this->update(
			'UPDATE flexible_roles
			SET 	press_id = ?,
				type = ?,
				enabled = ?
			WHERE 	flexible_role_id = ?',
			array(
				$role->getPressId(),
				$role->getType(),
				$role->getEnabled() ? 1 : 0,
				$role->getId()
			)
		);

		$this->updateLocaleFields($role);

		$this->update('DELETE FROM flexible_role_arrangements WHERE flexible_role_id = ?', $role->getId());

		foreach ($role->getAssociatedArrangements() as $arrangement) {
			$this->update(
				'INSERT INTO flexible_role_arrangements (flexible_role_id, arrangement_id)
				VALUES (?, ?)',
				array($role->getId(), $arrangement)
			);
		}
	}

	/**
	 * Set the associated workflow role arrangement ids.
	 * @param $role FlexibleRole
	 */
	function setAssociatedArrangements(&$role) {
		$result =& $this->retrieve(
			'SELECT arrangement_id FROM flexible_role_arrangements WHERE flexible_role_id = ?', $role->getId()
		);

		$returner = null;
		while (!$result->EOF) {
			$role->addAssociatedArrangement($result->fields['arrangement_id']);
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Soft delete a flexible role by id.
	 * @param $roleId int
	 */
	function deleteById($roleId) {
		return $this->update(
			'UPDATE flexible_roles
			SET enabled = 0
			WHERE flexible_role_id = ?', $roleId
		);
	}

	/**
	 * Get the ID of the last inserted flexible role.
	 * @return int
	 */
	function getInsertFlexibleRoleId() {
		return $this->getInsertId('flexible_roles', 'flexible_role_id');
	}
}

?>
