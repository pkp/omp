<?php
/**
 * @file classes/security/authorization/OmpPublishedMonographAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedMonographAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to published monographs.
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpPublishedMonographAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $monographParameterName string the request parameter we
	 *  expect the monograph id in.
	 */
	function OmpPublishedMonographAccessPolicy(&$request, $args, $roleAssignments, $monographParameterName = 'monographId') {
		parent::PressPolicy($request);

		// We need a published monograph in the request.
		import('classes.security.authorization.internal.MonographRequiredPolicy');
		$this->addPolicy(new MonographRequiredPolicy($request, $args, $monographParameterName));

		// We also need a published monograph in the request.
		import('classes.security.authorization.internal.PublishedMonographRequiredPolicy');
		$this->addPolicy(new PublishedMonographRequiredPolicy($request, $args, $monographParameterName));

		// Authors, press managers and series editors potentially have
		// pre-publication access to submissions. We'll have to define
		// differentiated policies for those roles in a policy set.
		$monographAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_MANAGER])) {
			// Press managers have access to all monographs.
			$monographAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));
		}


		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SERIES_EDITOR])) {
			// 1) Series editors can access all operations on monographs ...
			$seriesEditorSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$seriesEditorSubmissionAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));

			// 2) ... but only if the requested submission is part of their series.
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$seriesEditorSubmissionAccessPolicy->addPolicy(new SeriesAssignmentPolicy($request));
			$monographAccessPolicy->addPolicy($seriesEditorSubmissionAccessPolicy);
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
			$monographAccessPolicy->addPolicy($authorSubmissionAccessPolicy);
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
			$monographAccessPolicy->addPolicy($pressSubmissionAccessPolicy);
		}

		$this->addPolicy($monographAccessPolicy);
	}
}

?>
