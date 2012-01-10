<?php
/**
 * @file classes/security/authorization/internal/MonographFileAssignedAuditorAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileAssignedAuditorAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to check if the current user is an assigned
 * 	auditor of the file.
 *
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileAssignedAuditorAccessPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileAssignedAuditorAccessPolicy(&$request, $fileIdAndRevision = null) {
		parent::MonographFileBaseAccessPolicy($request, $fileIdAndRevision);
	}


	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		$request =& $this->getRequest();

		// Get the user
		$user =& $request->getUser();
		if (!is_a($user, 'PKPUser')) return AUTHORIZATION_DENY;

		// Get the monograph file
		$monographFile =& $this->getMonographFile($request);
		if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffsFactory =& $signoffDao->getAllByAssocType(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId(), null, $user->getId());

		if ($signoffsFactory->wasEmpty()) {
			return AUTHORIZATION_DENY;
		} else {
			return AUTHORIZATION_PERMIT;
		}
	}
}

?>
