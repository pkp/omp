<?php
/**
 * @file classes/security/authorization/OmpPublishedSubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedSubmissionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to published submissions in OMP.
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');

class OmpPublishedSubmissionAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we
	 * @param $publishedSubmissionsOnly boolean whether the OmpPublishedSubmissionRequiredPolicy has to be considered/added
	 *  expect the submission id in.
	 */
	function __construct($request, $args, $roleAssignments, $submissionParameterName = 'submissionId', $publishedSubmissionsOnly = true) {
		parent::__construct($request);

		// Access may be made either as a member of the public, or
		// via pre-publication access to editorial users.
		$monographAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		// Published submission access for the public
		$publishedSubmissionAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		if ($publishedSubmissionsOnly) {
			import('classes.security.authorization.OmpPublishedSubmissionRequiredPolicy');
			$publishedSubmissionAccessPolicy->addPolicy(new OmpPublishedSubmissionRequiredPolicy($request, $args, $submissionParameterName));
		} else {
			import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
			$publishedSubmissionAccessPolicy->addPolicy(new SubmissionRequiredPolicy($request, $args, $submissionParameterName));
		}
		$monographAccessPolicy->addPolicy($publishedSubmissionAccessPolicy);

		// Pre-publication access for editorial roles
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$monographAccessPolicy->addPolicy(
			new SubmissionAccessPolicy(
				$request, $args,
				array_intersect_key(
					$roleAssignments,
					array( // Only permit these roles
						ROLE_ID_MANAGER,
						ROLE_ID_SUB_EDITOR,
					)
				),
				$submissionParameterName
			)
		);

		$this->addPolicy($monographAccessPolicy);
	}
}


