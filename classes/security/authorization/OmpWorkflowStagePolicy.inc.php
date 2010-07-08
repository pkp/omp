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

import('lib.pkp.classes.security.authorization.OmpPressPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.HandlerOperationRolesPolicy');

class OmpWorkflowStagePolicy extends OmpPressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function OmpWorkflowStagePolicy(&$request) {
		parent::OmpPressPolicy($request);

		// Create an "allow overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$workflowStagePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		// Press managers can access all operations for all submissions and all workflow stages.
		$workflowStagePolicy->addPolicy(new HandlerOperationRolesPolicy($request, ROLE_ID_PRESS_MANAGER));


		//
		// Series editor role
		//
		// 1) Series editors can access all operations ...
		$seriesEditorWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$seriesEditorWorkflowStagePolicy->addPolicy(new HandlerOperationRolesPolicy($request, ROLE_ID_SERIES_EDITOR));

		// 2) ... if the requested workflow stage has been assigned to them in the press settings ...
		import('lib.pkp.classes.security.authorization.WorkflowSettingsAssignmentPolicy');
		$seriesEditorWorkflowStagePolicy->addPolicy(new WorkflowSettingsAssignmentPolicy($request)); // FIXME
		$workflowStagePolicy->addPolicy($seriesEditorWorkflowStagePolicy);

		// 3) ... but only if the requested submission is part of their series.
		import('lib.pkp.classes.security.authorization.SeriesAssignmentPolicy');
		$seriesEditorWorkflowStagePolicy->addPolicy(new SeriesAssignmentPolicy($request)); // FIXME
		$workflowStagePolicy->addPolicy($seriesEditorWorkflowStagePolicy);


		//
		// Press role
		//
		// 1) Press role user groups can access only the following whitelisted operations ...
		$pressRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$pressRoleOperationsWhitelist = array('please fill'); // FIXME
		$pressRoleWorkflowStagePolicy->addPolicy(new HandlerOperationRolesPolicy($request, ROLE_ID_PRESS_ASSISTANT, 'This workflow stage operation has not been whitelisted for press role user groups!', $pressRoleOperationsWhitelist));

		// 2) ... and only if the requested workflow stage has been assigned to them in the requested submission.
		import('lib.pkp.classes.security.authorization.WorkflowSubmissionAssignmentPolicy');
		$pressRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request)); // FIXME
		$workflowStagePolicy->addPolicy($pressRoleWorkflowStagePolicy);


		//
		// Author role
		//
		// 1) Author role user groups can access only the following whitelisted operations ...
		$authorRoleWorkflowStagePolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		$authorRoleOperationsWhitelist = array('please fill'); // FIXME
		$authorRoleWorkflowStagePolicy->addPolicy(new HandlerOperationRolesPolicy($request, ROLE_ID_AUTHOR, 'This workflow stage operation has not been whitelisted for authors!', $authorRoleOperationsWhitelist));

		// 2) ... if the requested submission is their own ...
		import('lib.pkp.classes.security.authorization.SubmissionAuthorPolicy');
		$authorRoleWorkflowStagePolicy->addPolicy(new SubmissionAuthorPolicy($request)); // FIXME
		$workflowStagePolicy->addPolicy($authorRoleWorkflowStagePolicy);

		// 3) ... and only if the requested workflow stage has been assigned to them in the requested submission.
		import('lib.pkp.classes.security.authorization.WorkflowSubmissionAssignmentPolicy');
		$authorRoleWorkflowStagePolicy->addPolicy(new WorkflowSubmissionAssignmentPolicy($request)); // FIXME
		$workflowStagePolicy->addPolicy($authorRoleWorkflowStagePolicy);


		// Add the role-specific policies to this policy set.
		$this->addPolicy($workflowStagePolicy);
	}
}

?>
