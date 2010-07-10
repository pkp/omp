<?php
/**
 * @file classes/security/authorization/OmpWorkflowStagePolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpWorkflowStagePolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission workflow stage components
 */

import('classes.security.authorization.OmpPressPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpWorkflowStagePolicy extends OmpPressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 */
	function OmpWorkflowStagePolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId') {
		parent::OmpPressPolicy($request);

		// A workflow stage component can only be called if there's a
		// valid series editor submission in the request.
		import('classes.security.authorization.SeriesEditorSubmissionRequiredPolicy');
		$this->addPolicy(new SeriesEditorSubmissionRequiredPolicy($request, $args, $submissionParameterName));

		// Create an "allow overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$workflowStagePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		// Press managers can access all operations for all submissions and all workflow stages.
		$workflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));


		//
		// Series editor role
		//
		// 1) Series editors can access all operations ...
		$seriesEditorWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$seriesEditorWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));

		// 2) ... if the requested workflow stage has been assigned to them in the press settings ...
		import('classes.security.authorization.WorkflowSettingsAssignmentPolicy');
		$seriesEditorWorkflowStagePolicy->addPolicy(new WorkflowSettingsAssignmentPolicy($request));
		$workflowStagePolicy->addPolicy($seriesEditorWorkflowStagePolicy);

		// 3) ... but only if the requested submission is part of their series.
		import('classes.security.authorization.SeriesAssignmentPolicy');
		$seriesEditorWorkflowStagePolicy->addPolicy(new SeriesAssignmentPolicy($request));
		$workflowStagePolicy->addPolicy($seriesEditorWorkflowStagePolicy);


		//
		// Press role
		//
		// 1) Press role user groups can access only the following whitelisted operations ...
		$pressRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$pressRoleWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_ASSISTANT, $roleAssignments[ROLE_ID_PRESS_ASSISTANT]));

		// 2) ... and only if the requested workflow stage has been assigned to them in the requested submission.
		import('classes.security.authorization.WorkflowSubmissionAssignmentPolicy');
		$pressRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request));
		$workflowStagePolicy->addPolicy($pressRoleWorkflowStagePolicy);


		//
		// Author role
		//
		// 1) Author role user groups can access only the following whitelisted operations ...
		$authorRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$authorRoleWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_AUTHOR, $roleAssignments[ROLE_ID_AUTHOR]));

		// 2) ... if the requested submission is their own ...
		import('classes.security.authorization.MonographAuthorPolicy');
		$authorRoleWorkflowStagePolicy->addPolicy(new MonographAuthorPolicy($request));
		$workflowStagePolicy->addPolicy($authorRoleWorkflowStagePolicy);

		// 3) ... and only if the requested workflow stage has been assigned to them in the requested submission.
		import('classes.security.authorization.WorkflowSubmissionAssignmentPolicy');
		$authorRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request));
		$workflowStagePolicy->addPolicy($authorRoleWorkflowStagePolicy);


		// Add the role-specific policies to this policy set.
		$this->addPolicy($workflowStagePolicy);
	}
}

?>
