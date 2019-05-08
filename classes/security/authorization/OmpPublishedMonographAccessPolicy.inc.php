<?php
/**
 * @file classes/security/authorization/OmpPublishedMonographAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedMonographAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to published monographs in OMP.
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');

class OmpPublishedMonographAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we
	 * @param $publishedMonographsOnly boolean whether the OmpPublishedMonographRequiredPolicy has to be considered/added
	 *  expect the submission id in.
	 */
	function __construct($request, $args, $roleAssignments, $submissionParameterName = 'submissionId', $publishedMonographsOnly = true) {
		parent::__construct($request);

		// Access may be made either as a member of the public, or
		// via pre-publication access to editorial users.
		$monographAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		// Published monograph access for the public
		$publishedMonographAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		if ($publishedMonographsOnly) {
			import('classes.security.authorization.OmpPublishedMonographRequiredPolicy');
			$publishedMonographAccessPolicy->addPolicy(new OmpPublishedMonographRequiredPolicy($request, $args, $submissionParameterName));
		} else {
			import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
			$publishedMonographAccessPolicy->addPolicy(new SubmissionRequiredPolicy($request, $args, $submissionParameterName));
		}
		$monographAccessPolicy->addPolicy($publishedMonographAccessPolicy);

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


