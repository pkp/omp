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


define('FLEXIBLE_ROLE_DEFAULT_PATH',	'role');

import('role.FlexibleRole');
import('press.DefaultSettingDAO');

class FlexibleRoleDAO extends DefaultSettingDAO
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
	 * Retrieve flexible roles to which a press user is assigned.
	 * @param $userId int
	 * @param $pressId int
	 * @return array FlexibleRole
	 */
	function &getByUserId($userId, $pressId){
		$result =& $this->retrieve(
			'SELECT fr.* FROM flexible_roles fr 
			LEFT JOIN roles r ON (r.flexible_role_id = fr.flexible_role_id)
			WHERE r.user_id = ? AND r.press_id = ?', array($userId, $pressId));

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
	 * Retrieve a flexible role by the path.
	 * @param $path string
	 * @param $pressId int
	 * @return FlexibleRole
	 */
	function getByPath($path, $pressId){
		$result =& $this->retrieve('SELECT * FROM flexible_roles WHERE press_id = ? AND path = ?', array($pressId, $path));
		
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve a flexible role by ROLE_ID_.
	 * @param $roleId string
	 * @param $pressId int
	 * @return FlexibleRole
	 */
	function getByRoleId($roleId, $pressId){
		$result =& $this->retrieve('SELECT * FROM flexible_roles WHERE press_id = ? AND role_id = ?', array($pressId, $roleId));
		
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
	function &getEnabledByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT fr.* FROM flexible_roles fr
			LEFT JOIN flexible_role_settings frs ON (frs.flexible_role_id = fr.flexible_role_id AND frs.locale = ? AND frs.setting_name = ?) 
			WHERE enabled = 1 AND press_id = ? ORDER BY CHAR_LENGTH(frs.setting_value) ASC', array(Locale::getLocale(), 'name', $pressId)
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
	 * @param $arrangementId int
	 * @param $pressId int
	 * @return mixed array
	 */
	function &getByArrangementId($arrangementId, $pressId) {

		$sql = 'SELECT fr.* FROM flexible_role_arrangements fra
			LEFT JOIN flexible_roles fr ON (fr.flexible_role_id = fra.flexible_role_id)
			WHERE fr.enabled = 1 AND fr.press_id = ? AND fra.arrangement_id = ?';

		$result =& $this->retrieve($sql, array($pressId, $arrangementId));

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
	 * Retrieve all flexible roles ids by arrangement id.
	 * @param $arrangementId int
	 * @param $pressId int
	 * @return mixed array
	 */
	function &getIdsByArrangementId($arrangementId, $pressId) {

		$sql = 'SELECT fr.flexible_role_id FROM flexible_role_arrangements fra
			LEFT JOIN flexible_roles fr ON (fr.flexible_role_id = fra.flexible_role_id)
			WHERE fr.enabled = 1 AND fr.press_id = ? AND fra.arrangement_id = ?';

		$result =& $this->retrieve($sql, array($pressId, $arrangementId));

		$returner = null;

		while (!$result->EOF) {
			$returner[] =& $result->fields['flexible_role_id'];
			$result->moveNext();
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
		return array('name', 'designation', 'pluralName');
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
		$flexibleRole->setPath($row['path']);
		$flexibleRole->setRoleId($row['role_id']);
		$flexibleRole->setCustomRole($row['custom_role']);

		$this->getDataObjectSettings('flexible_role_settings', 'flexible_role_id', $row['flexible_role_id'], $flexibleRole);

		$this->setAssociatedArrangements($flexibleRole);

		return $flexibleRole;
	}  

	/**
	 * Insert a new flexible role.
	 * @param $role FlexibleRole
	 */	
	function insertObject(&$role) {
		import('security.Role');

		$this->update(
			'INSERT INTO flexible_roles
				(press_id, type, enabled, role_id)
			VALUES
				(?, ?, ?, ?)',
			array(
				$role->getPressId(),
				$role->getType(),
				$role->getEnabled() ? 1 : 0,
				ROLE_ID_FLEXIBLE_ROLE
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

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'flexible_role_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'flexible_roles';
	}

	/**
	 * Get the column name of the primary key
	 * @return string
	 */
	function getPrimaryKeyColumnName() {
		return 'flexible_role_id';
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return DEFAULT_SETTING_FLEXIBLE_ROLES;
	}

	/**
	 * Get the path of the setting data file.
	 * @return string
	 */
	function getDefaultBaseFilename() {
		return 'registry/roles.xml';
	}


	/**
	 * Install book file types from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('role'));
		if (!isset($data['role'])) return false;

		foreach ($data['role'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO flexible_roles
				(entry_key, type, press_id, path, role_id, custom_role)
				VALUES
				(?, ?, ?, ?, ?, ?)',
				array($attrs['key'], $attrs['type'], $pressId, $attrs['path'], hexdec($attrs['roleId']), 0)
			);
		}
		return true;
	}

	/**
	 * Get setting names and values.
	 * @param $node XMLNode
	 * @param $locale string
	 * @return array
	 */
	function &getSettingAttributes($node = null, $locale = null) {
		if ($node == null) {
			$settings = array('name', 'designation', 'pluralName');
		} else {
			$localeKey = $node->getAttribute('localeKey');

			$settings = array(
				'name' => Locale::translate($localeKey, array(), $locale), 
				'designation' => Locale::translate($localeKey.'.designation', array(), $locale),
				'pluralName' => Locale::translate($localeKey.'s', array(), $locale)
			);
		}
		return $settings;
	}
}

?>
