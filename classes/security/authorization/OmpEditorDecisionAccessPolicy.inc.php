<?php
/**
 * @file classes/security/authorization/OmpEditorDecisionAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpEditorDecisionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission workflow stage components
 */

import('classes.security.authorization.internal.PressPolicy');

class OmpEditorDecisionAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function OmpEditorDecisionAccessPolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId', $stageId) {
		parent::PressPolicy($request);

		// A decision can only be made if there is a valid workflow stage
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy(&$request, &$args, $roleAssignments, $submissionParameterName, $stageId));

		// An editor decision can only be made if there is a press editor assigned to the stage
		import('classes.security.authorization.internal.PressManagerRequiredPolicy');
		$this->addPolicy(new PressManagerRequiredPolicy($request));
	}
}

?>
