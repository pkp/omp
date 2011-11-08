<?php
/**
 * @file classes/security/authorization/OmpWorkflowStageAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpWorkflowStageAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission workflow stage components
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpWorkflowStageAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function OmpWorkflowStageAccessPolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId', $stageId) {
		parent::PressPolicy($request);

		// A workflow stage component requires a valid workflow stage.
		import('classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));

		// A workflow stage component can only be called if there's a
		// valid series editor submission in the request.
		import('classes.security.authorization.internal.SeriesEditorSubmissionRequiredPolicy');
		$this->addPolicy(new SeriesEditorSubmissionRequiredPolicy($request, $args, $submissionParameterName));

		// Create a "permit overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$workflowStagePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_MANAGER])) {
			// Press managers can access all whitelisted operations for all submissions and all workflow stages.
			$workflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));
		}


		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SERIES_EDITOR])) {
			// 1) Series editors can access whitelisted operations ...
			$seriesEditorWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$seriesEditorWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));

			// 2) ... if the requested workflow stage has been assigned to them in the press settings ...
			import('classes.security.authorization.internal.WorkflowSettingsAssignmentPolicy');
			$seriesEditorWorkflowStagePolicy->addPolicy(new WorkflowSettingsAssignmentPolicy($request));

			// 3) ... but only if the requested submission is part of their series.
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$seriesEditorWorkflowStagePolicy->addPolicy(new SeriesAssignmentPolicy($request));
			$workflowStagePolicy->addPolicy($seriesEditorWorkflowStagePolicy);
		}


		//
		// Press role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_ASSISTANT])) {
			// 1) Press role user groups can access whitelisted operations ...
			$pressRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$pressRoleWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_ASSISTANT, $roleAssignments[ROLE_ID_PRESS_ASSISTANT]));

			// 2) ... but only if the requested workflow stage has been assigned to them in the requested submission.
			import('classes.security.authorization.internal.WorkflowSubmissionAssignmentPolicy');
			$pressRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request, $stageId));
			$workflowStagePolicy->addPolicy($pressRoleWorkflowStagePolicy);
		}


		//
		// Author role
		//
		if (isset($roleAssignments[ROLE_ID_AUTHOR])) {
			// 1) Author role user groups can access whitelisted operations ...
			$authorRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$authorRoleWorkflowStagePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_AUTHOR, $roleAssignments[ROLE_ID_AUTHOR]));

			// 2) ... only if the requested workflow stage has been assigned to them in the requested submission.
			import('classes.security.authorization.internal.WorkflowSubmissionAssignmentPolicy');
			$authorRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request, $stageId));
			$workflowStagePolicy->addPolicy($authorRoleWorkflowStagePolicy);
		}


		// Add the role-specific policies to this policy set.
		$this->addPolicy($workflowStagePolicy);
	}
}

?>
