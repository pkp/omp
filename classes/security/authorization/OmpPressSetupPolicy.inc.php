<?php
/**
 * @file classes/security/authorization/OmpPressSetupPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPressSetupPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's press setup components
 */

import('lib.pkp.classes.security.authorization.OmpPressPolicy');

class OmpPressSetupPolicy extends OmpPressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function OmpPressSetupPolicy(&$request) {
		parent::OmpPressPolicy($request);

		// Only press managers may access setup pages.
		import('lib.pkp.classes.security.authorization.HandlerOperationRolesPolicy');
		$this->addPolicy(new HandlerOperationRolesPolicy($request, ROLE_ID_PRESS_MANAGER, 'You are not a press manager!'));
	}
}

?>
