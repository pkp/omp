<?php
/**
 * @file classes/security/authorization/internal/WorkflowSettingsAssignmentPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowSettingsAssignmentPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to OMP's workflow stages based on
 *  user-group workflow step assignments made during press setup.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');
import('classes.workflow.UserGroupStageAssignment');

class WorkflowSettingsAssignmentPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function WorkflowSettingsAssignmentPolicy(&$request) {
		parent::AuthorizationPolicy();
		$this->_request =& $request;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		$router =& $this->_request->getRouter();

		// Get the press.
		$press =& $router->getContext($this->_request);
		if (!is_a($press, 'Press')) return AUTHORIZATION_DENY;

		// Get the authorized user group.
		$userGroup = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_GROUP);
		if (!is_integer($userGroup, 'UserGroup')) return AUTHORIZATION_DENY;

		// Retrieve the requested workflow stage.
		switch(true) {
			case is_a($router, 'PKPPageRouter'):
				// We expect the requested page to be a valid workflow path.
				$stagePath = $router->getRequestedPage($this->_request);
				break;

			case is_a($router, 'PKPComponentRouter'):
				// We expect a named 'workflowStage' argument.
				$stagePath = $this->_request->getUserVar('workflowStage');
				break;

			default:
				assert(false);
		}
		$stageId = UserGroupStageAssignment::getIdFromPath($stagePath);
		if (!is_integer($stageId)) return AUTHORIZATION_DENY;

		// Only grant access to workflow stages that have been explicitly
		// assigned to the authorized user group in the press setup.
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');
		if ($userGroupStageAssignmentDao->assignmentExists($press->getId(), $userGroup->getId(), $stageId)) {
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
