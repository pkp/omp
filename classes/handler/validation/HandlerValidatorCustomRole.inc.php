<?php
/**
 * @file classes/handler/HandlerValidatorPress.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 */

import('handler.validation.HandlerValidator');

class HandlerValidatorCustomRole extends HandlerValidator {

	/**
	 * Constructor.
	 * @param $handler Handler the associated form
	 */	 
	function HandlerValidatorCustomRole(&$handler, $redirectLogin = true, $message = null, $additionalArgs = array()) {
		parent::HandlerValidator($handler, $redirectLogin, $message, $additionalArgs);
	}

	/**
	 * Check to make sure the user is assigned to the custom role.
	 * @return boolean
	 */
	function isValid() {
		$customRoleId = Request::getUserVar('roleId');

		$session =& Request::getSession();
		$sessionRoleId = $session->getSessionVar('customRoleId');

		if (empty($customRoleId)) {
			$customRoleId = empty($sessionRoleId) ? null : $sessionRoleId;
		}

		if (!$customRoleId) return false;

		$user = Request::getUser();
		if ( !$user ) return false;

		$session->setSessionVar('customRoleId', $customRoleId);

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$press =& Request::getPress();
		$returner = false;

		if ( $customRoleId == ROLE_ID_SITE_ADMIN ) {
			$returner = $roleDao->roleExists(0, $user->getId(), ROLE_ID_FLEXIBLE_ROLE, $customRoleId);
		} else { 
			$returner = $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_FLEXIBLE_ROLE, $customRoleId);
		}

		return $returner;
	}
}

?>