<?php
/**
 * @file classes/security/authorization/OmpReviewPagePolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpReviewPagePolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's review page components
 */

import('classes.security.authorization.OmpPressPolicy');

class OmpReviewPagePolicy extends OmpPressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $roleAssignments array
	 */
	function OmpReviewPagePolicy(&$request, $roleAssignments) {
		parent::OmpPressPolicy($request);

		// Only reviewers may access the review page.
		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
		$this->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_REVIEWER, $roleAssignments[ROLE_ID_REVIEWER], 'You are not a reviewer!'));
	}
}

?>
