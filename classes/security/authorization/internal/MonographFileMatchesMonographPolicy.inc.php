<?php
/**
 * @file classes/security/authorization/internal/MonographFileMatchesMonographPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileMatchesMonographPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to check if the file belongs to the monograph
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileMatchesMonographPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileMatchesMonographPolicy(&$request) {
		parent::MonographFileBaseAccessPolicy($request);
	}


	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the monograph file
		$request =& $this->getRequest();
		$monographFile =& $this->getMonographFile($request);
		if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Check if the monograph file belongs to the monograph.
		if ($monographFile->getMonographId() == $monograph->getId()) {
			// Save the monograph to the authorization context.
			$this->addAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile);
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
