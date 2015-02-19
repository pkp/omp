<?php

/**
 * @file classes/security/Role.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Role
 * @ingroup security
 * @see RoleDAO
 *
 * @brief Describes user roles within the system and the associated permissions.
 */

import('lib.pkp.classes.security.PKPRole');

/** ID codes and paths for OMP-specific roles */
define('ROLE_ID_SERIES_EDITOR',		0x00000201);
define('ROLE_PATH_SERIES_EDITOR',	'seriesEditor');

/** Fill in the blanks for roles used in PKP lib */
define('ROLE_ID_SUB_EDITOR',		ROLE_ID_SERIES_EDITOR);

class Role extends PKPRole {
	/**
	 * Constructor.
	 * @param $roleId for this role.  Default to null for backwards
	 * 	compatibility
	 */
	function Role($roleId = null) {
		parent::PKPRole($roleId);
	}


	//
	// Get/set methods
	//

	/**
	 * Get the i18n key name associated with the specified role.
	 * @param $plural boolean get the plural form of the name
	 * @return string
	 */
	function getRoleName($plural = false) {
		switch ($this->getId()) {
			case ROLE_ID_SERIES_EDITOR:
				return 'user.role.seriesEditor' . ($plural ? 's' : '');
			default:
				return parent::getRoleName($plural);
		}
	}


	/**
	 * Get the URL path associated with the specified role's operations.
	 * @return string
	 */
	function getPath() {
		switch ($this->getId()) {
			case ROLE_ID_SERIES_EDITOR:
				return 'seriesEditor';
			default:
				return parent::getPath();
		}
	}
}

?>
