<?php
/**
 * @file classes/security/authorization/OmpPublishedMonographAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedMonographAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to published monographs in OMP.
 */

import('classes.security.authorization.internal.PressPolicy');

class OmpPublishedMonographAccessPolicy extends PressPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we
	 *  expect the submission id in.
	 */
	function OmpPublishedMonographAccessPolicy(&$request, $args, $roleAssignments, $submissionParameterName = 'monographId') {
		parent::PressPolicy($request);

		// Access may be made either as a member of the public, or
		// via pre-publication access to editorial users.
		$monographAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		// Published monograph access for the public
		$publishedMonographAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		import('classes.security.authorization.internal.MonographRequiredPolicy');
		$publishedMonographAccessPolicy->addPolicy(new MonographRequiredPolicy($request, $args, $submissionParameterName));
		import('classes.security.authorization.internal.MonographPublishedPolicy');
		$publishedMonographAccessPolicy->addPolicy(new MonographPublishedPolicy($request));
		$monographAccessPolicy->addPolicy($publishedMonographAccessPolicy);

		// Pre-publication access for editorial roles
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$monographAccessPolicy->addPolicy(
			new OmpSubmissionAccessPolicy(
				$request, $args,
				array_intersect_key(
					$roleAssignments,
					array( // Only permit these roles
						ROLE_ID_PRESS_MANAGER,
						ROLE_ID_PRESS_EDITOR,
					)
				),
				$submissionParameterName
			)
		);

		$this->addPolicy($monographAccessPolicy);
	}
}

?>
