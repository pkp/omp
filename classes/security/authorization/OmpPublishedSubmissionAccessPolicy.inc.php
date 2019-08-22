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
	 */
	function __construct($request, $args, $roleAssignments, $submissionParameterName = 'submissionId') {
		parent::__construct($request);

		// Require published submissions
		import('classes.security.authorization.OmpPublishedSubmissionRequiredPolicy');
		$this->addPolicy(new OmpPublishedSubmissionRequiredPolicy($request, $args, $submissionParameterName));
	}
}


