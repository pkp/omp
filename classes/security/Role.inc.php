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

/** ID codes and paths for all default roles */
define('ROLE_ID_SITE_ADMIN',		0x00000001);
define('ROLE_PATH_SITE_ADMIN', 'admin');

define('ROLE_ID_PRESS_MANAGER',		0x00000011);
define('ROLE_PATH_PRESS_MANAGER', 'manager');

define('ROLE_ID_SERIES_EDITOR',	0x00000201);
define('ROLE_PATH_SERIES_EDITOR', 'seriesEditor');

define('ROLE_ID_AUTHOR',		0x00010000);
define('ROLE_PATH_AUTHOR', 'author');

define('ROLE_ID_REVIEWER',		0x00001000);
define('ROLE_PATH_REVIEWER', 'reviewer');


//FIXME: The following new role (="press role") will have to be introduced - see #6113.
define('ROLE_ID_PRESS_ASSISTANT', 0x00001001);


//FIXME: The following roles will have to be deleted - see #6113.
define('ROLE_ID_DIRECTOR',		0x00000012);
define('ROLE_PATH_DIRECTOR', 'director');

define('ROLE_ID_EDITOR',		0x00000100);
define('ROLE_PATH_EDITOR', 'editor');

define('ROLE_ID_PRODUCTION_EDITOR',	0x00000401);
define('ROLE_PATH_PRODUCTION_EDITOR', 'productionEditor');

define('ROLE_ID_COPYEDITOR',		0x00002000);
define('ROLE_PATH_COPYEDITOR', 'copyeditor');

define('ROLE_ID_PROOFREADER',		0x00004000);
define('ROLE_PATH_PROOFREADER', 'proofreader');

define('ROLE_ID_READER',		0x00020000);
define('ROLE_PATH_READER', 'reader');

define('ROLE_ID_DESIGNER',		0x00100001);
define('ROLE_PATH_DESIGNER', 'designer');

define('ROLE_ID_MARKETING',		0x00100010);
define('ROLE_PATH_MARKETING', 'marketing');

define('ROLE_ID_FUNDING_COORDINATOR',	0x00100100);
define('ROLE_PATH_FUNDING_COORDINATOR', 'funding');

define('ROLE_ID_INDEXER',		0x00200000);
define('ROLE_PATH_INDEXER', 'indexer');


class Role extends DataObject {

	/**
	 * Constructor.
	 * @param $roleId for this role.  Default to null for backwards compatibility
	 */
	function Role($roleId = null) {
		parent::DataObject();
		$this->setId($roleId);
	}

	//
	// Get/set methods
	//

	/**
	 * Get role ID of this role.
	 * @return int
	 */
	function getRoleId() {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->getId();
	}

	/**
	 * Set role ID of this role.
	 * @param $roleId int
	 */
	function setRoleId($roleId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->setId($roleId);
	}

	/**
	 * Get role path of this role.
	 * @param $roleId int
	 * @return int
	 */
	function getRolePath($roleId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->getPath();
	}

	/**
	 * Get the i18n key name associated with the specified role.
	 * @param $plural boolean get the plural form of the name
	 * @return string
	 */
	function getRoleName($plural = false) {
		switch ($this->getId()) {
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
			case ROLE_ID_DESIGNER:
				return 'user.role.designer' . ($plural ? 's' : '');
			case ROLE_ID_COPYEDITOR:
				return 'user.role.copyeditor' . ($plural ? 's' : '');
			case ROLE_ID_PROOFREADER:
				return 'user.role.proofreader' . ($plural ? 's' : '');
			case ROLE_ID_PRODUCTION_EDITOR:
				return 'user.role.productionEditor' . ($plural ? 's' : '');
			case ROLE_ID_READER:
				return 'user.role.reader' . ($plural ? 's' : '');
			case ROLE_ID_DIRECTOR:
				return 'user.role.director' . ($plural ? 's' : '');
			case ROLE_ID_INDEXER:
				return 'user.role.indexer' . ($plural ? 's' : '');
			default:
				return '';
		}
	}

	/**
	 * Get the URL path associated with the specified role's operations.
	 * @return string
	 */
	function getPath() {
		switch ($this->getId()) {
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
			case ROLE_ID_DESIGNER:
				return 'designer';
			case ROLE_ID_COPYEDITOR:
				return 'copyeditor';
			case ROLE_ID_PROOFREADER:
				return 'proofreader';
			case ROLE_ID_PRODUCTION_EDITOR:
				return 'productionEditor';
			case ROLE_ID_READER:
				return 'reader';
			case ROLE_ID_DIRECTOR:
				return 'director';
			case ROLE_ID_INDEXER:
				return 'indexer';
			default:
				return '';
		}
	}

}

?>