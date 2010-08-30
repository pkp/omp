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
 *
 * NB: Expects an already authorized submission and user group
 * in the authorization context.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class WorkflowSubmissionAssignmentPolicy extends AuthorizationPolicy {
	/** @var Request */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $anyStep boolean true if the requested submission is assigned
	 *  to any workflow step for the requested submission.
	 */
	function WorkflowSubmissionAssignmentPolicy(&$request) {
		$this->_request =& $request;
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

		// Retrieve the user.
		$user =& $this->_request->getUser();
		if (!is_a($user, 'User')) return AUTHORIZATION_DENY;

		// Retrieve the authorized submission.
		if (!$this->hasAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH)) return AUTHORIZATION_DENY;
		$submission =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve the authorized user group.
		if (!$this->hasAuthorizedContextObject(ASSOC_TYPE_USER_GROUP)) return AUTHORIZATION_DENY;
		$userGroup =& $this->getAuthorizedContextObject(ASSOC_TYPE_USER_GROUP);

		// Retrieve the workflow step from the request.
		// FIXME.

		// Deny access if no valid workflow step was found in the request.
		// FIXME.

		// Check whether the user is assigned to the submission in the current
		// user group for the given workflow step.
		// FIXME.

		// Access has been authorized.
		return AUTHORIZATION_PERMIT;
	}
}

?>
