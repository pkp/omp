<?php
/**
 * @file classes/security/authorization/SubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control (write) access to submissions and (read) access to
 * submission details in OMP.
 */

import('lib.pkp.classes.security.authorization.PKPSubmissionAccessPolicy');

class SubmissionAccessPolicy extends PKPSubmissionAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we
	 *  expect the submission id in.
	 */
	function SubmissionAccessPolicy($request, $args, $roleAssignments, $submissionParameterName = 'submissionId') {
		parent::PKPSubmissionAccessPolicy($request, $args, $roleAssignments, $submissionParameterName);

		$submissionAccessPolicy = $this->_baseSubmissionAccessPolicy;

		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SUB_EDITOR])) {
			// 1) Series editors can access all operations on submissions ...
			$subEditorSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$subEditorSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SUB_EDITOR, $roleAssignments[ROLE_ID_SUB_EDITOR]));

			// but only if ...
			$subEditorAssignmentOrSeriesPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

			// 2a) ... the requested submission is part of their series ...
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$subEditorAssignmentOrSeriesPolicy->addPolicy(new SeriesAssignmentPolicy($request));

			// 2b) ... or they have been assigned to the requested submission.
			import('classes.security.authorization.internal.UserAccessibleWorkflowStageRequiredPolicy');
			$subEditorAssignmentOrSeriesPolicy->addPolicy(new UserAccessibleWorkflowStageRequiredPolicy($request));

			$subEditorSubmissionAccessPolicy->addPolicy($subEditorAssignmentOrSeriesPolicy);
			$submissionAccessPolicy->addPolicy($subEditorSubmissionAccessPolicy);
		}

		$this->addPolicy($submissionAccessPolicy);
	}
}

?>
