<?php
/**
 * @file classes/security/authorization/internal/WorkflowSettingsAssignmentPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowSettingsAssignmentPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to OMP's workflow stages based on
 *  user-group workflow step assignments made during press setup.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class WorkflowSettingsAssignmentPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function WorkflowSettingsAssignmentPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.workflowStageSettingMissing');
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
		$user =& $this->_request->getUser();

		// Get the press.
		$press =& $router->getContext($this->_request);
		if (!is_a($press, 'Press')) return AUTHORIZATION_DENY;

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		// Retrieve the requested workflow stage.
		switch(true) {
			case is_a($router, 'PKPPageRouter'):
				// We expect the requested page to be a valid workflow path.
				$stagePath = $router->getRequestedOp($this->_request);
				$stageId = $userGroupDao->getIdFromPath($stagePath);

				// If not, try to get it from the stageId parameter.
				if (!$stageId) $stageId = (int) $this->_request->getUserVar('stageId');
				break;

			case is_a($router, 'PKPComponentRouter'):
				// We expect a named 'stageId' argument.
				$stageId = (int) $this->_request->getUserVar('stageId');
				break;

			default:
				fatalError('Unknown stage type.');
		}
		if (!is_integer($stageId)) return AUTHORIZATION_DENY;

		if (!is_a($user, 'User')) return AUTHORIZATION_DENY;

		// Only grant access to workflow stages that have been explicitly
		// assigned to the authorized user group in the press setup.
		if ($userGroupDao->userAssignmentExists($press->getId(), $user->getId(), $stageId)) {
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
