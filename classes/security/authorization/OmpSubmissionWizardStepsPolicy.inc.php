<?php
/**
 * @file classes/security/authorization/OmpSubmissionWizardStepsPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSubmissionWizardMainPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission wizard components
 */

import('classes.security.authorization.OmpSubmissionWizardPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpSubmissionWizardStepsPolicy extends OmpSubmissionWizardPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function OmpSubmissionWizardStepsPolicy(&$request, $args, $roleAssignments) {
		parent::OmpSubmissionWizardPolicy($request, $args, $roleAssignments);

		// Create a "deny overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$submissionWizardPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);

		// We can have no monograph at all, or the current user has to be the monograph owner
		$monographPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		// The first page of the submission has no monograph associated with it
		import('classes.security.authorization.MonographNotPresentPolicy');
		$monographPolicy->addPolicy(new MonographNotPresentPolicy($request, $args));

		// 1) There must be a monograph in the request and the current user must be the owner
		import('classes.security.authorization.MonographRequiredAndUserAuthorPolicy');
		$monographPolicy->addPolicy(new MonographRequiredAndUserAuthorPolicy($request, $args));

		$submissionWizardPolicy->addPolicy($monographPolicy);

		// a policy set that combines valid monograph
		import('classes.security.authorization.SubmissionStepsPolicy');
		$submissionWizardPolicy->addPolicy(new SubmissionStepsPolicy($request, $args));

		$this->addPolicy($submissionWizardPolicy);
	}
}

?>
