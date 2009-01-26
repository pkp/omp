<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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
		$this->userDao = &DAORegistry::getDAO('UserDAO');
	}

	/**
	 * Retrieve a role.
	 * @param $pressId int
	 * @param $userId int
	 * @param $roleId int
	 * @return Role
	 */
	function &getRole($pressId, $userId, $roleId) {
		$result = &$this->retrieve(
			'SELECT * FROM roles WHERE press_id = ? AND user_id = ? AND role_id = ?',
			array(
				(int) $pressId,
				(int) $userId,
				(int) $roleId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnRoleFromRow($result->GetRowAssoc(false));
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
		$role = &new Role();
		$role->setPressId($row['press_id']);
		$role->setUserId($row['user_id']);
		$role->setRoleId($row['role_id']);

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
				(press_id, user_id, role_id)
				VALUES
				(?, ?, ?)',
			array(
				(int) $role->getPressId(),
				(int) $role->getUserId(),
				(int) $role->getRoleId()
			)
		);
	}

	/**
	 * Delete all roles for a specified press.
	 * @param $userId int
	 * @param $pressId int optional, include roles only for this press
	 * @param $roleId int optional, include only this role
	 */
	function deleteRoleByUserId($userId, $pressId  = null, $roleId = null) {
		return $this->update(
			'DELETE FROM roles WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : '') . (isset($roleId) ? ' AND role_id = ?' : ''),
			isset($pressId) && isset($roleId) ? array((int) $userId, (int) $pressId, (int) $roleId)
			: (isset($pressId) ? array((int) $userId, (int) $pressId)
			: (isset($roleId) ? array((int) $userId, (int) $roleId) : (int) $userId))
		);
	}
	/**
	 * Delete a role.
	 * @param $role Role
	 */
	function deleteRole(&$role) {
		return $this->update(
			'DELETE FROM roles WHERE press_id = ? AND user_id = ? AND role_id = ?',
			array(
				(int) $role->getPressId(),
				(int) $role->getUserId(),
				(int) $role->getRoleId()
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

		$result = &$this->retrieve(
			'SELECT * FROM roles WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : ''),
			isset($pressId) ? array((int) $userId, (int) $pressId) : ((int) $userId)
		);

		while (!$result->EOF) {
			$roles[] = &$this->_returnRoleFromRow($result->GetRowAssoc(false));
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
	 * @return array matching Users
	 */
	function &getUsersByRoleId($roleId = null, $pressId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
		$users = array();

		$paramArray = array('interests');
		if (isset($roleId)) $paramArray[] = (int) $roleId;
		if (isset($pressId)) $paramArray[] = (int) $pressId;

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

		if (isset($search) && isset($searchTypeMap[$searchType])) {
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
		} elseif (isset($search)) switch ($searchType) {
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

		$result = &$this->retrieveRange(
			'SELECT DISTINCT u.* FROM users AS u LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?), roles AS r WHERE u.user_id = r.user_id ' . (isset($roleId)?'AND r.role_id = ?':'') . (isset($pressId) ? ' AND r.press_id = ?' : '') . ' ' . $searchSql,
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

		if (isset($search) && isset($searchTypeMap[$searchType])) {
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
		} elseif (isset($search)) switch ($searchType) {
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

		$result = &$this->retrieveRange(

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
		$userDao = &DAORegistry::getDAO('UserDAO');

		$result = &$this->retrieve(
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
	 */
	function &getRolesByPressId($pressId = null, $roleId = null) {
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

		$result = &$this->retrieve(
			'SELECT * FROM roles' . (empty($conditions) ? '' : ' WHERE ' . join(' AND ', $conditions)),
			$params
		);

		$returner = &new DAOResultFactory($result, $this, '_returnRoleFromRow');
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
	 * Delete all roles for the specified user.
	 * @param $userId int
	 * @param $pressId int optional, include roles only in this press
	 * @param $roleId int optional, include only this role
	 */
	function deleteRolesByUserId($userId, $pressId  = null, $roleId = null) {
		return $this->update(
			'DELETE FROM roles WHERE user_id = ?' . (isset($pressId) ? ' AND press_id = ?' : '') . (isset($roleId) ? ' AND role_id = ?' : ''),
			isset($pressId) && isset($roleId) ? array((int) $userId, (int) $pressId, (int) $roleId)
			: (isset($pressId) ? array((int) $userId, (int) $pressId)
			: (isset($roleId) ? array((int) $userId, (int) $roleId) : (int) $userId))
		);
	}

	/**
	 * Check if a role exists.
	 * @param $pressId int
	 * @param $userId int
	 * @param $roleId int
	 * @return boolean
	 */
	function roleExists($pressId, $userId, $roleId) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) FROM roles WHERE press_id = ? AND user_id = ? AND role_id = ?', array((int) $pressId, (int) $userId, (int) $roleId)
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
	 * @return string
	 */
	function getRoleName($roleId, $plural = false) {
		switch ($roleId) {
			case ROLE_ID_SITE_ADMIN:
				return 'user.role.siteAdmin' . ($plural ? 's' : '');
			case ROLE_ID_PRESS_MANAGER:
				return 'user.role.manager' . ($plural ? 's' : '');
			case ROLE_ID_AUTHOR:
				return 'user.role.author' . ($plural ? 's' : '');
			case ROLE_ID_EDITOR:
				return 'user.role.editor' . ($plural ? 's' : '');
			case ROLE_ID_REVIEWER:
				return 'user.role.reviewer' . ($plural ? 's' : '');
			case ROLE_ID_SERIES_EDITOR:
				return 'user.role.seriesEditor' . ($plural ? 's' : '');
			case ROLE_ID_LAYOUT_EDITOR:
				return 'user.role.layoutEditor' . ($plural ? 's' : '');
			case ROLE_ID_COPYEDITOR:
				return 'user.role.copyeditor' . ($plural ? 's' : '');
			case ROLE_ID_PROOFREADER:
				return 'user.role.proofreader' . ($plural ? 's' : '');
			default:
				return '';
		}
	}

	/**
	 * Get the URL path associated with the specified role's operations.
	 * @param $roleId int
	 * @return string
	 */
	function getRolePath($roleId) {
		switch ($roleId) {
			case ROLE_ID_SITE_ADMIN:
				return 'admin';
			case ROLE_ID_PRESS_MANAGER:
				return 'manager';
			case ROLE_ID_AUTHOR:
				return 'author';
			case ROLE_ID_EDITOR:
				return 'editor';
			case ROLE_ID_REVIEWER:
				return 'reviewer';
			case ROLE_ID_SERIES_EDITOR:
				return 'seriesEditor';
			case ROLE_ID_LAYOUT_EDITOR:
				return 'layoutEditor';
			case ROLE_ID_COPYEDITOR:
				return 'copyeditor';
			case ROLE_ID_PROOFREADER:
				return 'proofreader';
			default:
				return '';
		}
	}

	/**
	 * Get a role's ID based on its path.
	 * @param $rolePath string
	 * @return int
	 */
	function getRoleIdFromPath($rolePath) {
		switch ($rolePath) {
			case 'admin':
				return ROLE_ID_SITE_ADMIN;
			case 'manager':
				return ROLE_ID_PRESS_MANAGER;
			case 'author':
				return ROLE_ID_AUTHOR;
			case 'editor':
				return ROLE_ID_EDITOR;
			case 'reviewer':
				return ROLE_ID_REVIEWER;
			case 'seriesEditor':
				return ROLE_ID_SERIES_EDITOR;
			case 'layoutEditor':
				return ROLE_ID_LAYOUT_EDITOR;
			case 'copyeditor':
				return ROLE_ID_COPYEDITOR;
			case 'proofreader':
				return ROLE_ID_PROOFREADER;
			default:
				return null;
		}
	}
}

?>
