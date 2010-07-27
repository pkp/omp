<?php
/**
 * @file classes/security/authorization/MonographNotPresentPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographNotPresentPolicy
 * @ingroup security_authorization
 *
 * @brief Policy that ensures that the request does NOT contain monograph.
 */

import('lib.pkp.classes.security.authorization.SubmissionRequiredPolicy');

class MonographNotPresentPolicy extends SubmissionRequiredPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographNotPresentPolicy(&$request, &$args, $submissionParameterName = 'monographId') {
		parent::SubmissionRequiredPolicy($request, $args, $submissionParameterName, 'Invalid monograph or no monograph requested!');
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the monograph id.
		$monographId = $this->getSubmissionId();
		if ($monographId === false) return AUTHORIZATION_PERMIT;

		return AUTHORIZATION_DENY;
	}
}

?>
