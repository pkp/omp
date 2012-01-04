<?php
/**
 * @file classes/security/authorization/internal/UserAccessibleWorkflowStageRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserAccessibleWorkflowStagesRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a signoff for the current press
 *
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class UserAccessibleWorkflowStageRequiredPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function UserAccessibleWorkflowStageRequiredPolicy(&$request) {
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
		$request =& $this->_request;
		$press =& $request->getContext();
		$pressId = $press->getId();
		$user =& $request->getUser();
		if (!is_a($user, 'User')) return AUTHORIZATION_DENY;

		$userId = $user->getId();
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$workflowStages = $userGroupDao->getWorkflowStageTranslationKeys();

		$accessibleWorkflowStages = array();
		foreach ($workflowStages as $stageId => $translationKey) {
			$accessibleStageRoles = $this->_getAccessibleStageRoles($userId, $pressId, &$monograph, $stageId);
			if (!empty($accessibleStageRoles)) {
				$accessibleWorkflowStages[$stageId] = $accessibleStageRoles;
			}
		}

		if (empty($accessibleWorkflowStages)) {
			return AUTHORIZATION_DENY;
		} else {
			$this->addAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES, $accessibleWorkflowStages);
			return AUTHORIZATION_PERMIT;
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Check for roles that give access to the passed workflow stage.
	 * @param int $userId
	 * @param int $pressId
	 * @param Monograph $monograph
	 * @param int $stageId
	 * @return array
	 */
	function _getAccessibleStageRoles($userId, $pressId, &$monograph, $stageId) {
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		$accessibleStageRoles = array();
		foreach ($userRoles as $roleId) {
			switch ($roleId) {
				case ROLE_ID_PRESS_MANAGER:
					// Press managers have access to all submission stages.
					$accessibleStageRoles[] = $roleId;
					break;

				case ROLE_ID_SERIES_EDITOR:
					// The requested submission must be part of their series...
					// and the requested workflow stage must be assigned to
					// them in the press settings.
					import('classes.security.authorization.internal.SeriesAssignmentRule');
					if (SeriesAssignmentRule::effect($pressId, $monograph->getSeriesId(), $userId) &&
					$userGroupDao->userAssignmentExists($pressId, $userId, $stageId)) {
						$accessibleStageRoles[] = $roleId;
					}
					break;

				case ROLE_ID_PRESS_ASSISTANT:
				case ROLE_ID_AUTHOR:
					// The requested workflow stage has been assigned to them
					// in the requested submission.
					$stageAssignments =& $stageAssignmentDao->getBySubmissionAndRoleId($monograph->getId(), $roleId, $stageId, $userId);
					if(!$stageAssignments->wasEmpty()) {
						$accessibleStageRoles[] = $roleId;
					}
					break;
				default:
					break;
			}
		}
		return $accessibleStageRoles;
	}
}

?>
