<?php
/**
 * @file classes/security/authorization/OmpSignoffAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSignoffAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to signoffs in OMP.
 */

import('classes.security.authorization.internal.PressPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class OmpSignoffAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we expect
	 *  the submission id in.
	 */
	function OmpSignoffAccessPolicy(&$request, $args, $roleAssignments) {
		parent::PressPolicy($request);

		// We need a submission matching the file in the request.
		import('classes.security.authorization.internal.SignoffExistsAccessPolicy');
		$this->addPolicy(new SignoffExistsAccessPolicy($request, $args));

		// Authors, press managers and series editors potentially have
		// access to submission files. We'll have to define
		// differentiated policies for those roles in a policy set.
		$fileAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);


		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_PRESS_MANAGER])) {
			// Press managers have all access to all submissions.
			$fileAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_PRESS_MANAGER, $roleAssignments[ROLE_ID_PRESS_MANAGER]));
		}


		//
		// Series editor role
		//
		if (isset($roleAssignments[ROLE_ID_SERIES_EDITOR])) {
			// 1) Series editors can access all operations on submissions ...
			$seriesEditorFileAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$seriesEditorFileAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SERIES_EDITOR, $roleAssignments[ROLE_ID_SERIES_EDITOR]));

			// 2) ... but only if the requested submission is part of their series.
			import('classes.security.authorization.internal.SeriesAssignmentPolicy');
			$seriesEditorFileAccessPolicy->addPolicy(new SeriesAssignmentPolicy($request));
			$fileAccessPolicy->addPolicy($seriesEditorFileAccessPolicy);
		}


		//
		// User owns the signoff (all roles): permit
		//
		import('classes.security.authorization.internal.SignoffAssignedToUserAccessPolicy');
		$userOwnsSignoffPolicy = new SignoffAssignedToUserAccessPolicy($request);
		$fileAccessPolicy->addPolicy($userOwnsSignoffPolicy);
		$this->addPolicy($fileAccessPolicy);
	}
}

?>
