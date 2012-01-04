<?php
/**
 * @file classes/security/authorization/OmpWorkflowStageAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
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
	function OmpWorkflowStageAccessPolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId', $stageId = null) {
		parent::PressPolicy($request);

		// A workflow stage component requires a valid workflow stage.
		// If none is passed, then we check all stages.
		if (!is_null($stageId)) {
			import('classes.security.authorization.internal.WorkflowStageRequiredPolicy');
			$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));
		}

		// A workflow stage component can only be called if there's a
		// valid series editor submission in the request.
		import('classes.security.authorization.internal.SeriesEditorSubmissionRequiredPolicy');
		$this->addPolicy(new SeriesEditorSubmissionRequiredPolicy($request, $args, $submissionParameterName));

		// Add the user accessible workflow stages object to the authorized context.
		import('classes.security.authorization.internal.UserAccessibleWorkflowStageRequiredPolicy');
		$this->addPolicy(new UserAccessibleWorkflowStageRequiredPolicy($request, $roleAssignments));

		// Users can access all whitelisted operations for submissions and workflow stages...
		$roleBasedPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		foreach ($roleAssignments as $roleId => $operations) {
			$roleBasedPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $roleId, $operations));
		}
		$this->addPolicy($roleBasedPolicy);

		// ... if they can access the requested workflow stage.
		import('classes.security.authorization.internal.UserAccessibleWorkflowStagePolicy');
		$this->addPolicy(new UserAccessibleWorkflowStagePolicy($stageId));
	}
}

?>
