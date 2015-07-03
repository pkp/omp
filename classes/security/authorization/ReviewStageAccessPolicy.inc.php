<?php
/**
 * @file classes/security/authorization/ReviewStageAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewStageAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's review stage components
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');

class ReviewStageAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function ReviewStageAccessPolicy($request, &$args, $roleAssignments, $submissionParameterName = 'submissionId', $stageId) {
		parent::ContextPolicy($request);

		// Create a "permit overrides" policy set that specifies
		// role-specific access to submission stage operations.
		$workflowStagePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		// Add the workflow policy, for editorial / press roles
		import('classes.security.authorization.WorkflowStageAccessPolicy');
		$workflowStagePolicy->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, $submissionParameterName, $stageId));

		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			// Add the submission policy, for reviewer roles
			import('classes.security.authorization.SubmissionAccessPolicy');
			$submissionPolicy = new SubmissionAccessPolicy($request, $args, $roleAssignments, $submissionParameterName);
			$submissionPolicy->addPolicy(new WorkflowStageRequiredPolicy($stageId));
			$workflowStagePolicy->addPolicy($submissionPolicy);
		}

		// Add the role-specific policies to this policy set.
		$this->addPolicy($workflowStagePolicy);
	}
}

?>
