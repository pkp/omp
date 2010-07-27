<?php
/**
 * @file classes/security/authorization/OmpSubmissionWizardMonographPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSubmissionWizardMonographPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission wizard components
 */

import('classes.security.authorization.OmpSubmissionWizardPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpSubmissionWizardMonographPolicy extends OmpSubmissionWizardPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function OmpSubmissionWizardMonographPolicy(&$request, $args, $roleAssignments) {
		parent::OmpSubmissionWizardPolicy($request, $args, $roleAssignments);

		// There must be a monograph in the request and the current user must be the owner
		import('classes.security.authorization.MonographRequiredAndUserAuthorPolicy');
		$this->addPolicy(new MonographRequiredAndUserAuthorPolicy($request, $args));
	}
}

?>
