<?php
/**
 * @file classes/security/authorization/MonographRequiredAndUserAuthorPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographRequiredAndUserAuthorPolicy
 * @ingroup security_authorization
 *
 * @brief Class to ensure request contains a valid monograph
 * and to control access to a monograph based on authorship.
 *
 */

import('lib.pkp.classes.security.authorization.PolicySet');

class MonographRequiredAndUserAuthorPolicy extends PolicySet {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographRequiredAndUserAuthorPolicy(&$request, &$args, $submissionParameterName = 'monographId') {
		parent::PolicySet();
		import('classes.security.authorization.MonographRequiredPolicy');
		$this->addPolicy(new MonographRequiredPolicy($request, $args, $submissionParameterName));

		// 2) The monograph must be have been submitted by the user himself.
		import('classes.security.authorization.MonographAuthorPolicy');
		$this->addPolicy(new MonographAuthorPolicy($request));
	}
}

?>