<?php

/**
 * @file classes/security/Role.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Role
 * @ingroup security
 * @see RoleDAO
 *
 * @brief Describes user roles within the system and the associated permissions.
 */

// $Id$


/** ID codes for all user roles */
define('ROLE_ID_SITE_ADMIN',		0x00000001);
define('ROLE_ID_PRESS_MANAGER',		0x00000010);
define('ROLE_ID_EDITOR',                0x00000100);
define('ROLE_ID_ACQUISITIONS_EDITOR',	0x00000200);
define('ROLE_ID_PRODUCTION_EDITOR',	0x00000300);
define('ROLE_ID_REVIEWER',		0x00001000);
define('ROLE_ID_COPYEDITOR',		0x00002000);
define('ROLE_ID_PROOFREADER',		0x00003000);
define('ROLE_ID_AUTHOR',                0x00010000);
define('ROLE_ID_READER',		0x00100000);
define('ROLE_ID_DESIGNER',		0x01000000);
define('ROLE_ID_COMMITTEE_MEMBER',	0x02000000);
define('ROLE_ID_DIRECTOR',		0x03000000);
define('ROLE_ID_INDEXER',		0x04000000);

class Role extends DataObject {

	/**
	 * Constructor.
	 */
	function Role() {
		parent::DataObject();
	}

	/**
	 * Get the i18n key name associated with this role.
	 * @return String the key
	 */
	function getRoleName() {
		return RoleDAO::getRoleName($this->getData('roleId'));
	}

	/**
	 * Get the URL path associated with this role's operations.
	 * @return String the path
	 */
	function getRolePath() {
		return RoleDAO::getRolePath($this->getData('roleId'));
	}

	//
	// Get/set methods
	//

	/**
	 * Get press ID associated with role.
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set press ID associated with role.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get user ID associated with role.
	 * @return int
	 */
	function getUserId() {
		return $this->getData('userId');
	}

	/**
	 * Set user ID associated with role.
	 * @param $userId int
	 */
	function setUserId($userId) {
		return $this->setData('userId', $userId);
	}

	/**
	 * Get role ID of this role.
	 * @return int
	 */
	function getRoleId() {
		return $this->getData('roleId');
	}

	/**
	 * Set role ID of this role.
	 * @param $roleId int
	 */
	function setRoleId($roleId) {
		return $this->setData('roleId', $roleId);
	}
}

?>
