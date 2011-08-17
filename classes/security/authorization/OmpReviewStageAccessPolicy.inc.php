<?php
/**
 * @file classes/security/authorization/OmpReviewStageAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpReviewStageAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's review stage components
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');

class OmpReviewStageAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function OmpReviewStageAccessPolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId', $stageId) {
		parent::PressPolicy($request);

		// Create a "permit overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$workflowStagePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		// Add the workflow policy, for editorial / press roles
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$workflowStagePolicy->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, $submissionParameterName, $stageId));

		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			// Add the submission policy, for reviewer roles
			import('classes.security.authorization.OmpSubmissionAccessPolicy');
			$submissionPolicy = new OmpSubmissionAccessPolicy($request, $args, $roleAssignments, $submissionParameterName);
			$submissionPolicy->addPolicy(new WorkflowStageRequiredPolicy($stageId));
			$workflowStagePolicy->addPolicy($submissionPolicy);
		}

		// Add the role-specific policies to this policy set.
		$this->addPolicy($workflowStagePolicy);
	}
}

?>
