<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

// $Id$


import('security.Role');

class RoleDAO extends DAO {
	/**
	 * Constructor.
	 */
	function RoleDAO() {
		parent::DAO();
		$this->userDao =& DAORegistry::getDAO('UserDAO');
	}

	/**
	 * Retrieve a role.
	 * @param $pressId int
	 * @param $userId int
	 * @param $roleId int
	 * @param $flexibleRoleId int
	 * @return Role
	 */
	function &getRole($pressId, $userId, $roleId, $flexibleRoleId = null) {

		if ($roleId == ROLE_ID_FLEXIBLE_ROLE && !$flexibleRoleId) return null;

		$sql = 'SELECT * FROM roles
			WHERE press_id = ? AND user_id = ? AND role_id = ?'. (isset($flexibleRoleId) ? ' AND flexible_role_id = ?' : '');
		$sqlParams = array((int) $pressId, (int) $userId, (int) $roleId);

		if ($flexibleRoleId) {
			$sqlParams[] = (int) $flexibleRoleId;
		}

		$result =& $this->retrieve($sql, $sqlParams);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnRoleFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a Role object from a row.
	 * @param $row array
	 * @return Role
	 */
	function &_returnRoleFromRow(&$row) {
		$role = new Role();
		$role->setPressId($row['press_id']);
		$role->setUserId($row['user_id']);
		$role->setRoleId($row['role_id']);
		$role->setFlexibleRoleId($row['flexible_role_id']);

		HookRegistry::call('RoleDAO::_returnRoleFromRow', array(&$role, &$row));

		return $role;
	}

	/**
	 * Insert a new role.
	 * @param $role Role
	 */
	function insertRole(&$role) {

		return $this->update(
			'INSERT INTO roles
				(press_id, user_id, role_id, flexible_role_id)
				VALUES
				(?, ?, ?, ?)',
			array(
				(int) $role->getPressId(),
				(int) $role->getUserId(),
				(int) $role->getRoleId(),
				(int) $role->getFlexibleRoleId()
			)
		);
	}

	/**
	 * Delete a user's roles.
	 * @param $userId int
	 * @param $pressId int optional, include roles only for this press
	 * @param $roleId int optional, include only this ROLE_ID
	 * @param $flexibleRoleId int optional, include only this role
	 */
	function deleteRoleByUserId($userId, $pressId  = null, $roleId = null, $flexibleRoleId = null) {
		$sql = 'DELETE FROM roles WHERE user_id = ? ';
		$sqlParams = array($userId);

		if (isset($pressId)) {
			$sql .= 'AND press_id = ? ';
			$sqlParams[] = $pressId;
		}
		if (isset($roleId)) {
			$sql .= 'AND role_id = ? ';
			$sqlParams[] = $roleId;
		}
		if (isset($flexibleRoleId)) {
			$sql .= 'AND flexible_role_id = ? ';
			$sqlParams[] = $flexibleRoleId;
		}

		return $this->update($sql, $sqlParams);
	}
	/**
	 * Delete a role.
	 * @param $role Role
	 */
	function deleteRole(&$role) {
		return $this->update(
			'DELETE FROM roles WHERE press_id = ? AND user_id = ? AND role_id = ? AND flexible_role_id = ?',
			array(
				(int) $role->getPressId(),
				(int) $role->getUserId(),
				(int) $role->getRoleId(),
				(int) $role->getFlexibleRoleId()
			)
		);
	}

	/**
	 * Retrieve a list of all roles for the specified user.
	 * @param $userId int
	 * @param $pressId int optional, include roles only in this press
	 * @return array matching Roles
	 */
	function &getRolesByUserId($userId, $pressId = null) {
		$roles = array();

		$result =& $this->retrieve(
			'SELECT * FROM roles WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : ''),
			isset($pressId) ? array((int) $userId, (int) $pressId) : ((int) $userId)
		);

		while (!$result->EOF) {
			$roles[] =& $this->_returnRoleFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $roles;
	}

	/**
	 * Retrieve a list of users in a specified role.
	 * @param $roleId int optional (can leave as null to get all users associated with a monograph project)
	 * @param $pressId int optional, include users associated only with this press
	 * @param $searchType int optional, which field to search
	 * @param $search string optional, string to match
	 * @param $searchMatch string optional, type of match ('is' vs. 'contains')
	 * @param $dbResultRange object DBRangeInfo object describing range of results to return
	 * @param $customRoleId int flexible role id of custom role
	 * @return array matching Users
	 */
	function &getUsersByRoleId($roleId = null, $pressId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null, $customRoleId = null) {
		$users = array();

		$paramArray = array('interests');
		if (isset($roleId)) $paramArray[] = (int) $roleId;
		if (isset($pressId)) $paramArray[] = (int) $pressId;
		if (isset($customRoleId)) $paramArray[] = (int) $customRoleId;
		// For security / resource usage reasons, a role or press ID
		// must be specified. Don't allow calls supplying neither.
		if ($pressId === null && $roleId === null) return null;

		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);

		if (!empty($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (!empty($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND u.user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
				$paramArray[] = $search . '%';
				break;
		}

		$searchSql .= ' ORDER BY u.last_name, u.first_name'; // FIXME Add "sort field" parameter?

		$result =& $this->retrieveRange(
			'SELECT DISTINCT u.* FROM users AS u 
			LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?), roles AS r 
			WHERE u.user_id = r.user_id' . (isset($roleId) ? ' AND r.role_id = ?' : '') . (isset($pressId) ? ' AND r.press_id = ?' : '') . (isset($customRoleId) ? ' AND r.flexible_role_id = ?' : '') . ' ' . $searchSql,
			$paramArray,
			$dbResultRange
		);

		$returner = new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
		return $returner;
	}

	/**
	 * Retrieve a list of all users with some role in the specified press.
	 * @param $pressId int
	 * @param $searchType int optional, which field to search
	 * @param $search string optional, string to match
	 * @param $searchMatch string optional, type of match ('is' vs. 'contains')
	 * @param $dbRangeInfo object DBRangeInfo object describing range of results to return
	 * @return array matching Users
	 */
	function &getUsersByPressId($pressId, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
		$users = array();

		$paramArray = array('interests', (int) $pressId);
		$searchSql = '';


		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);

		if (!empty($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (!empty($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND u.user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
				$paramArray[] = $search . '%';
				break;
		}

		$searchSql .= ' ORDER BY u.last_name, u.first_name'; // FIXME Add "sort field" parameter?

		$result =& $this->retrieveRange(

			'SELECT DISTINCT u.* FROM users AS u LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?), roles AS r WHERE u.user_id = r.user_id AND r.press_id = ? ' . $searchSql,
			$paramArray,
			$dbResultRange
		);

		$returner = new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
		return $returner;
	}

	/**
	 * Retrieve the number of users associated with the specified press.
	 * @param $monographId int
	 * @return int
	 */
	function getPressUsersCount($pressId) {
		$userDao =& DAORegistry::getDAO('UserDAO');

		$result =& $this->retrieve(
			'SELECT COUNT(DISTINCT(user_id)) FROM roles WHERE monograph_id = ?',
			(int) $monographId
		);

		$returner = $result->fields[0];

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Select all roles for a specific press.
	 * @param $monographId int optional
	 * @param $roleId int optional
	 * @param $flexibleRoleId int optional
	 */
	function &getRolesByPressId($pressId = null, $roleId = null, $flexibleRoleId = null) {
		$params = array();
		$conditions = array();
		if (isset($pressId)) {
			$params[] = (int) $pressId;
			$conditions[] = 'press_id = ?';
		}
		if (isset($roleId)) {
			$params[] = (int) $roleId;
			$conditions[] = 'role_id = ?';
		}
		if (isset($flexibleRoleId)) {
			$params[] = (int) $flexibleRoleId;
			$conditions[] = 'flexible_role_id = ?';
		}

		$result =& $this->retrieve(
			'SELECT * FROM roles' . (empty($conditions) ? '' : ' WHERE ' . join(' AND ', $conditions)),
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_returnRoleFromRow');
		return $returner;
	}

	/**
	 * Delete all roles for a specific press.
	 * @param $monographId int
	 */
	function deleteRolesByPressId($pressId) {
		return $this->update(
			'DELETE FROM roles WHERE press_id = ?', (int) $pressId
		);
	}

	/**
	 * Check if a role exists.
	 * @param $pressId int
	 * @param $userId int
	 * @param $roleId int
	 * @param $flexibleRoleId int
	 * @return boolean
	 */
	function roleExists($pressId, $userId, $roleId, $flexibleRoleId = null) {

		$sqlParams = array((int) $pressId, (int) $userId, (int) $roleId);
		if (isset($flexibleRoleId)) {
			$sqlParams[] = (int) $flexibleRoleId;
		}

		$result =& $this->retrieve(
			'SELECT COUNT(role_id) FROM roles 
			WHERE press_id = ? AND user_id = ? AND role_id = ?'.(isset($flexibleRoleId) ? ' AND flexible_role_id = ?': ''), 
			$sqlParams
		);

		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the i18n key name associated with the specified role.
	 * @param $roleId int
	 * @param $plural boolean get the plural form of the name
	 * @param $pressId int
	 * @param $flexibleRoleId int
	 * @return string
	 */
	function getRoleName($roleId, $plural = false, $pressId = null, $flexibleRoleId = null) {
		if ($roleId == ROLE_ID_SITE_ADMIN) {
			return $plural ? Locale::translate('user.role.siteAdmins') : Locale::translate('user.role.siteAdmin');
		}

		if (empty($pressId)) {
			$press =& Request::getPress();
			$pressId = $press ? $press->getId() : null;
		}

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$flexibleRole =& $flexibleRoleDao->getByRoleId($roleId, $pressId, $flexibleRoleId);
		if ($flexibleRole) {
			return $plural ? $flexibleRole->getLocalizedPluralName() : $flexibleRole->getLocalizedName();
 		}
		return '';
	}

	/**
	 * Get the URL path associated with the specified role's operations.
	 * @param $roleId int
	 * @param pressId int
	 * @return string
	 */
	function getRolePath($roleId, $pressId = null) {

		if ($roleId == ROLE_ID_SITE_ADMIN) {
			return 'admin';
		} 

		if (empty($pressId)) {
			$press =& Request::getPress();
			$pressId = $press ? $press->getId() : null;
		}

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$flexibleRole =& $flexibleRoleDao->getByRoleId($roleId, $pressId);
		if ($flexibleRole) {
			return $flexibleRole->getPath();
 		}
		return '';
	}

	/**
	 * Get a role's ID based on its path.
	 * @param $rolePath string
	 * @param $pressId int
	 * @return int
	 */
	function getRoleIdFromPath($rolePath, $pressId = null) {

		if ($roleId == 'admin') {
			return ROLE_ID_SITE_ADMIN;
		} 

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		if (empty($pressId)) {
			$press =& Request::getPress();
			$pressId = $press ? $press->getId() : null;
		}

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$flexibleRole =& $flexibleRoleDao->getByPath($rolePath, $pressId);
		if ($flexibleRole) {
			return $flexibleRole->getRoleId();
 		}
		return null;
	}
}

?>