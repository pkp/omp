<?php
/**
 * @file classes/security/authorization/OmpSubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSubmissionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control (write) access to submissions and (read) access to
 * submission details in OMP.
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpSubmissionAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we expect
	 *  the submission id in.
	 */
	function OmpSubmissionAccessPolicy(&$request, $args, $roleAssignments, $submissionParameterName = 'monographId') {
		parent::PressPolicy($request);

		// We need a submission in the request.
		import('classes.security.authorization.internal.MonographRequiredPolicy');
		$this->addPolicy(new MonographRequiredPolicy($request, $args, $submissionParameterName));

		// Authors, press managers and series editors potentially have access
		// to submissions. We'll have to define differentiated policies for those
		// roles in a policy set.
		$submissionAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_MANAGER])) {
			// Press managers have access to all submissions.
			$submissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));
		}


		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SERIES_EDITOR])) {
			// 1) Series editors can access all operations on submissions ...
			$seriesEditorSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$seriesEditorSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));

			// 2) ... but only if the requested submission is part of their series.
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$seriesEditorSubmissionAccessPolicy->addPolicy(new SeriesAssignmentPolicy($request));
			$submissionAccessPolicy->addPolicy($seriesEditorSubmissionAccessPolicy);
		}


		//
		// Author role
		//
		if (isset($roleAssignments[ROLE_ID_AUTHOR])) {
			// 1) Author role user groups can access whitelisted operations ...
			$authorSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$authorSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_AUTHOR, $roleAssignments[ROLE_ID_AUTHOR], 'user.authorization.authorRoleMissing'));

			// 2) ... if they meet one of the following requirements:
			$authorSubmissionAccessOptionsPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

			// 2a) ...the requested submission is their own ...
			import('classes.security.authorization.internal.MonographAuthorPolicy');
			$authorSubmissionAccessOptionsPolicy->addPolicy(new MonographAuthorPolicy($request));

			// 2b) ...OR, at least one workflow stage has been assigned to them in the requested submission.
			import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
			$authorSubmissionAccessOptionsPolicy->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', null));

			$authorSubmissionAccessPolicy->addPolicy($authorSubmissionAccessOptionsPolicy);
			$submissionAccessPolicy->addPolicy($authorSubmissionAccessPolicy);
		}


		//
		// Reviewer role
		//
		if (isset($roleAssignments[ROLE_ID_REVIEWER])) {
			// 1) Reviewers can access whitelisted operations ...
			$reviewerSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$reviewerSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_REVIEWER, $roleAssignments[ROLE_ID_REVIEWER]));

			// 2) ... but only if they have been assigned to the submission as reviewers.
			import('classes.security.authorization.internal.ReviewAssignmentAccessPolicy');
			$reviewerSubmissionAccessPolicy->addPolicy(new ReviewAssignmentAccessPolicy($request));
			$submissionAccessPolicy->addPolicy($reviewerSubmissionAccessPolicy);
		}

		//
		// Press role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_ASSISTANT])) {
			// 1) Press assistants can access whitelisted operations ...
			$pressSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$pressSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_ASSISTANT, $roleAssignments[ROLE_ID_PRESS_ASSISTANT]));

			// 2) ... but only if they have been assigned to the submission workflow.
			import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
			$pressSubmissionAccessPolicy->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', null));
			$submissionAccessPolicy->addPolicy($pressSubmissionAccessPolicy);
		}

		$this->addPolicy($submissionAccessPolicy);
	}
}

?>
