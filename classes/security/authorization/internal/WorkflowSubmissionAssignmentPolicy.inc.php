<?php
/**
 * @file classes/security/authorization/internal/WorkflowSubmissionAssignmentPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowSubmissionAssignmentPolicy
 * @ingroup security_authorization_internal
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

	/** @var int */
	var $_stageId;

	/** @var int */
	var $_roleId;

	/**
	 * Constructor
	 * @param $request Request
	 * @param $stageId integer (optional) the stage the user has to be assigned to.
	 * @param $roleId integer (optional) the role that the assignment user group must have.
	 */
	function WorkflowSubmissionAssignmentPolicy(&$request, $stageId = null, $roleId = null) {
		$this->_request =& $request;
		$this->_stageId = (int) $stageId;
		$this->_roleId = (int) $roleId;

		parent::AuthorizationPolicy('user.authorization.workflowStageAssignmentMissing');
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the user.
		$user =& $this->_request->getUser();
		if (!is_a($user, 'PKPUser')) return AUTHORIZATION_DENY;

		// Get the press.
		$router =& $this->_request->getRouter();
		$press =& $router->getContext($this->_request);
		if (!is_a($press, 'Press')) return AUTHORIZATION_DENY;

		// Get the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Check whether a valid workflow stage has been defined for this policy.
		if ($this->_stageId) {
			if ($this->_stageId < WORKFLOW_STAGE_ID_SUBMISSION || $this->_stageId > WORKFLOW_STAGE_ID_PRODUCTION) return AUTHORIZATION_DENY;
		}

		// Check whether the user is assigned to the submission in any capacity.
		// If a stage id and/or role id was given, use it to check specific stage assignment.
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageId = null;
		$roleId = null;
		if ($this->_stageId) $stageId = $this->_stageId;
		if ($this->_roleId) $roleId = $this->_roleId;
		$stageAssignments =& $stageAssignmentDao->getBySubmissionAndRoleId($monograph->getId(), $roleId, $stageId, $user->getId());
		if($stageAssignments->wasEmpty()) {
			return AUTHORIZATION_DENY;
		}

		// Access has been authorized.
		return AUTHORIZATION_PERMIT;
	}
}

?>
