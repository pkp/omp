<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

import('lib.pkp.classes.security.Role');
import('lib.pkp.classes.security.PKPRoleDAO');
import('lib.pkp.classes.security.UserGroupAssignment');

/** ID codes and paths for OMP-specific roles */
define('ROLE_ID_SERIES_EDITOR',		0x00000201);

/** Fill in the blanks for roles used in PKP lib */
define('ROLE_ID_SUB_EDITOR',		ROLE_ID_SERIES_EDITOR);

class RoleDAO extends PKPRoleDAO {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
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
	 * Get a mapping of role keys and i18n key names.
	 * @param boolean $contextOnly If false, also returns site-level roles (Site admin)
	 * @param array $roleIds Only return role names of these IDs
	 * @return array
	 */
	static function getRoleNames($contextOnly = false, $roleIds = null) {

		$parentRoleNames = parent::getRoleNames($contextOnly);

		$pressRoleNames = array(
			ROLE_ID_MANAGER => 'user.role.manager',
			ROLE_ID_SERIES_EDITOR => 'user.role.subEditor',
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
