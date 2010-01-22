<?php

/**
 * @file classes/security/Role.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
define('ROLE_ID_PRESS_MANAGER',		0x00000011);
define('ROLE_ID_DIRECTOR',		0x00000012);
define('ROLE_ID_EDITOR',		0x00000100);
define('ROLE_ID_ACQUISITIONS_EDITOR',	0x00000201);
define('ROLE_ID_PRODUCTION_EDITOR',	0x00000401);
define('ROLE_ID_REVIEWER',		0x00001000);
define('ROLE_ID_COPYEDITOR',		0x00002000);
define('ROLE_ID_PROOFREADER',		0x00004000);
define('ROLE_ID_AUTHOR',		0x00010000);
define('ROLE_ID_VOLUME_EDITOR',		0x00010001);
define('ROLE_ID_TRANSLATOR',		0x00010010);
define('ROLE_ID_READER',		0x00020000);
define('ROLE_ID_DESIGNER',		0x00100001);
define('ROLE_ID_MARKETING',		0x00100010);
define('ROLE_ID_FUNDING_COORDINATOR',	0x00100100);
define('ROLE_ID_INDEXER',		0x00200000);
define('ROLE_ID_FLEXIBLE_ROLE',		0x00400000);

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
		return RoleDAO::getRoleName($this->getData('roleId'), false, $this->getData('pressId'), $this->getData('flexibleRoleId'));
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

	/**
	 * Get the flexible role id of this role.
	 * @return int
	 */
	function getFlexibleRoleId() {
		$flexibleRoleId = $this->getData('flexibleRoleId');
		if (!isset($flexibleRoleId) && $this->getData('roleId') !== ROLE_ID_FLEXIBLE_ROLE) {
			$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
			$flexibleRole = $flexibleRoleDao->getByRoleId($this->getRoleId(), $this->getPressId());
			$flexibleRoleId = $flexibleRole ? $flexibleRole->getId() : null;
		}
		return $flexibleRoleId;
	}

	/**
	 * Set the flexible role id of this role.
	 * @param $flexibleRoleId int
	 */
	function setFlexibleRoleId($flexibleRoleId) {
		return $this->setData('flexibleRoleId', $flexibleRoleId);
	}
}

?>
