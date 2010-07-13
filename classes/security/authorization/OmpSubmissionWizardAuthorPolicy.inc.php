<?php
/**
 * @file classes/security/authorization/OmpSubmissionWizardAuthorPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpSubmissionWizardAuthorPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission wizard components
 *  that require a monograph in the request of which the current user
 *  is the author.
 */

import('classes.security.authorization.OmpSubmissionWizardPolicy');

class OmpSubmissionWizardAuthorPolicy extends OmpSubmissionWizardPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 */
	function OmpSubmissionWizardAuthorPolicy(&$request, &$args, $roleAssignments, $submissionParameterName = 'monographId') {
		parent::OmpSubmissionWizardPolicy($request, $roleAssignments);

		// 1) There must be a monograph in the request.
		import('classes.security.authorization.MonographRequiredPolicy');
		$this->addPolicy(new MonographRequiredPolicy($request, $args, $submissionParameterName));

		// 2) The monograph must be have been submitted by the user himself.
		import('classes.security.authorization.MonographAuthorPolicy');
		$this->addPolicy(new MonographAuthorPolicy($request));
	}
}

?>
