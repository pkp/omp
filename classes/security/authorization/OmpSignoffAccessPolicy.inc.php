<?php
/**
 * @file classes/security/authorization/OmpSignoffAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSignoffAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to signoffs in OMP.
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

define('SIGNOFF_ACCESS_READ', 1);
define('SIGNOFF_ACCESS_MODIFY', 2);

class OmpSignoffAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $mode int bitfield SIGNOFF_ACCESS_...
	 * @param $stageId int
	 */
	function OmpSignoffAccessPolicy($request, $args, $roleAssignments, $mode, $stageId) {
		parent::ContextPolicy($request);

		// We need a submission matching the file in the request.
		import('lib.pkp.classes.security.authorization.internal.SignoffExistsAccessPolicy');
		$this->addPolicy(new SignoffExistsAccessPolicy($request, $args));

		// We need a valid workflow stage.
		import('lib.pkp.classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));

		// Authors, press managers and series editors potentially have
		// access to signoffs. We'll have to define
		// differentiated policies for those roles in a policy set.
		$signoffAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_MANAGER])) {
			// Press managers have all access to all signoffs.
			$signoffAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_MANAGER, $roleAssignments[ROLE_ID_MANAGER]));
		}


		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SUB_EDITOR])) {
			// 1) Series editors can access all operations on signoffs ...
			$seriesEditorFileAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$seriesEditorFileAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SUB_EDITOR, $roleAssignments[ROLE_ID_SUB_EDITOR]));

			// 2) ... but only if the requested signoff submission is part of their series.
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$seriesEditorFileAccessPolicy->addPolicy(new SeriesAssignmentPolicy($request));
			$signoffAccessPolicy->addPolicy($seriesEditorFileAccessPolicy);
		}


		//
		// Press assistants
		//
		if (isset($roleAssignments[ROLE_ID_ASSISTANT])) {
			// 1) Press assistants can access all operations on signoffs...
			$pressAssistantSignoffAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$pressAssistantSignoffAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_ASSISTANT, $roleAssignments[ROLE_ID_ASSISTANT]));

			// 2) ... but only if they have access to the workflow stage.
			import('classes.security.authorization.WorkflowStageAccessPolicy');
			$pressAssistantSignoffAccessPolicy->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));
			$signoffAccessPolicy->addPolicy($pressAssistantSignoffAccessPolicy);
		}


		//
		// Authors
		//
		if (isset($roleAssignments[ROLE_ID_AUTHOR])) {
			if ($mode & SIGNOFF_ACCESS_READ) {
				// 1) Authors can access read operations on signoffs...
				$authorSignoffAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
				$authorSignoffAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_AUTHOR, $roleAssignments[ROLE_ID_AUTHOR]));

				// 2) ... but only if they are assigned to the workflow stage as an stage participant.
				import('classes.security.authorization.WorkflowStageAccessPolicy');
				$authorSignoffAccessPolicy->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));
				$signoffAccessPolicy->addPolicy($authorSignoffAccessPolicy);
			}
		}

		//
		// User owns the signoff (all roles): permit
		//
		import('classes.security.authorization.internal.SignoffAssignedToUserAccessPolicy');
		$userOwnsSignoffPolicy = new SignoffAssignedToUserAccessPolicy($request);
		$signoffAccessPolicy->addPolicy($userOwnsSignoffPolicy);
		$this->addPolicy($signoffAccessPolicy);
	}
}

?>
