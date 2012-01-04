<?php
/**
 * @file classes/security/authorization/OmpAuthorDashboardAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpAuthorDashboardAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP author dashboard.
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpAuthorDashboardAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 */
	function OmpAuthorDashboardAccessPolicy(&$request, &$args, $roleAssignments) {
		parent::PressPolicy($request);

		$authorDashboardPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);

		// AuthorDashboard requires a valid monograph in request.
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$authorDashboardPolicy->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments), true);

		// Check if the user has an stage assignment with the monograph in request.
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$authorDashboardPolicy->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $request->getUserVar('stageId')));

		$this->addPolicy($authorDashboardPolicy);
	}
}

?>
