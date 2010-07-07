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

import('lib.pkp.classes.security.authorization.PolicySet');

class OmpPressSetupPolicy extends PolicySet {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function OmpPressSetupPolicy(&$request) {
		parent::PolicySet();

		// 1) Ensure we're in a press
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request, 'No press in context!'));

		// 2) Only Press Managers and Admins may access
		import('lib.pkp.classes.security.authorization.HandlerOperationRolesPolicy');
		$this->addPolicy(new HandlerOperationRolesPolicy($request, array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SITE_ADMIN), 'Insufficient privileges!'));
	}
}

?>
