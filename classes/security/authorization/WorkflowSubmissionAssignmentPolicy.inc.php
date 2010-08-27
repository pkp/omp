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
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function WorkflowSubmissionAssignmentPolicy(&$request) {
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
		$userGroupDAO =& DAORegistry::getDAO('UserGroupDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		// Get the user
		$user =& $this->_request->getUser();
		if (!is_a($user, 'PKPUser')) return AUTHORIZATION_DENY;

		// Get the press
		$router =& $this->_request->getRouter();
		$press =& $router->getContext($this->_request);
		if (!is_a($press, 'Press')) return AUTHORIZATION_DENY;

		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Get the monograph's current stage
		$stageId = $monograph->getCurrentStageId();


		// Permit if the user is in a managerial role *for the current press*
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		if ($roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_PRESS_MANAGER)) {
			return AUTHORIZATION_PERMIT;
		}


		// Permit if the user is in a series editor role and assigned to the series in the current
		//  press and *the workflow stage is assigned to series editors in the press settings*
		if (isset($seriesId) && $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_SERIES_EDITOR)) {
			// Check that series editors are allowed into the current stage (as configured in setup step 3)
			$userGroupStageAssignmentDAO =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');
			$seriesEditorUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_SERIES_EDITOR);
			if($userGroupStageAssignmentDAO->assignmentExists($press->getId(), $seriesEditorUserGroup->getId(), $stageId)) {
				// Check that user is a series editor for the monograph's current series
				$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
				if ($seriesEditorDao->editorExists($press->getId(), $monograph->getSeriesId(), $user->getId())) {
					return AUTHORIZATION_PERMIT;
				}
			}
		}

		// *Press roles* permitted only when explicitly assigned to the submission in that workflow stage
		// Check that user is assigned to current stage
		foreach($userGroupDao->getByRoleId($press->getId(), ROLE_ID_PRESS_ASSISTANT) as $userGroup) {
			if($signoffDao->assignmentExists('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $userId, $stageId, $userGroup->getId())) {
				return AUTHORIZATION_PERMIT;
			}
		}

		// *Author roles* permitted only when explicitly assigned to the submission in that workflow stage
		foreach($userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR) as $userGroup) {
			if($signoffDao->assignmentExists('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $userId, $stageId, $userGroup->getId())) {
				return AUTHORIZATION_PERMIT;
			}
		}

		// Reviewers, public users and site admins (i.e. all others) do never have access to workflow pages.
		return AUTHORIZATION_DENY;
	}
}

?>
