<?php
/**
 * @file classes/security/authorization/OmpPressAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPressAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's press-level components
 */

import('classes.security.authorization.internal.PressPolicy');

class OmpPressAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function OmpPressAccessPolicy(&$request, $roleAssignments) {
		parent::PressPolicy($request);

		// On press level we don't have role-specific conditions
		// so we can simply add all role assignments. It's ok if
		// any of these role conditions permits access.
		$pressRolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
		foreach($roleAssignments as $role => $operations) {
			$pressRolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
		}
		$this->addPolicy($pressRolePolicy);
	}
}

?>
