<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

import('classes.security.Role');
import('lib.pkp.classes.security.PKPRoleDAO');
import('lib.pkp.classes.security.UserGroupAssignment');

class RoleDAO extends PKPRoleDAO {

	/**
	 * Constructor.
	 */
	function RoleDAO() {
		parent::PKPRoleDAO();
	}

	/**
	 * Create new data object.
	 * @return Role
	 */
	function newDataObject() {
		return new Role();
	}

	/**
	 * Retrieve the number of users with a given role associated with the specified press.
	 * @param $pressId int
	 * @param $roleId int
	 * @return int
	 */
	function getPressUsersRoleCount($pressId, $roleId) {
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		return $userGroupDao->getContextUsersCount($pressId, null, $roleId);
	}

	/**
	 * Get a role's ID based on its path.
	 * @param $rolePath string
	 * @return int
	 */
	function getRoleIdFromPath($rolePath) {
		switch ($rolePath) {
			case 'seriesEditor':
				return ROLE_ID_SERIES_EDITOR;
			default:
				return parent::getRoleIdFromPath($rolePath);
		}
	}

	/**
	 * Get a mapping of role keys and i18n key names.
	 * @param boolean $contextOnly If false, also returns site-level roles (Site admin)
	 * @param array $roleIds Only return role names of these IDs
	 * @return array
	 */
	static function getRoleNames($contextOnly = false, $roleIds = null) {

		$parentRoleNames = parent::getRoleNames($contextOnly);

		$pressRoleNames = array(
			ROLE_ID_MANAGER => 'user.role.manager',
			ROLE_ID_SERIES_EDITOR => 'user.role.seriesEditor',
			ROLE_ID_ASSISTANT => 'user.role.assistant',
		);
		$roleNames = $parentRoleNames + $pressRoleNames;

		if(!empty($roleIds)) {
			$returner = array();
			foreach($roleIds as $roleId) {
				if(isset($roleNames[$roleId])) $returner[$roleId] = $roleNames[$roleId];
			}
			return $returner;
		} else {
			return $roleNames;
		}
	}
}

?>
