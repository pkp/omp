<?php
/**
 * @file classes/security/authorization/OmpSubmissionWizardPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSubmissionWizardPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission wizard components
 */

import('classes.security.authorization.OmpPressPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpSubmissionWizardPolicy extends OmpPressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function OmpSubmissionWizardPolicy(&$request, $args, $roleAssignments) {
		parent::OmpPressPolicy($request);

		// Authors, series editors and press managers are allowed to submit.
		$rolesPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		$rolesPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));
		$rolesPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));
		$rolesPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_AUTHOR, $roleAssignments[ROLE_ID_AUTHOR]));
		$this->addPolicy($rolesPolicy);
	}
}

?>
