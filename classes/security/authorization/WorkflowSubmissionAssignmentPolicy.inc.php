<?php
/**
 * @file classes/security/authorization/WorkflowSubmissionAssignmentPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowSubmissionAssignmentPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's workflow stages based on
 *  user-group - user - submission - workflow step assignments.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class WorkflowSubmissionAssignmentPolicy extends AuthorizationPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function WorkflowSubmissionAssignmentPolicy(&$request) {
		parent::AuthorizationPolicy();
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// FIXME: Implement when workflow submission assignments have been implemented, see #5557.
		return AUTHORIZATION_PERMIT;
	}
}

?>
