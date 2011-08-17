<?php
/**
 * @file classes/security/authorization/internal/MonographFileViewableReviewerResponseAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileViewableReviewerResponseAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to check if the current user is an assigned
 * 	reviewer of the file.
 *
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileViewableReviewerResponseAccessPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileViewableReviewerResponseAccessPolicy(&$request) {
		parent::MonographFileBaseAccessPolicy($request);
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

		// Make sure that it's in the review stage
		if ($monographFile->getFileStage() != MONOGRAPH_FILE_REVIEW_ATTACHMENT) return AUTHORIZATION_DENY;

		// Make sure this is the monograph's author
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;
		if ($monograph->getId() != $monographFile->getSubmissionId()) return AUTHORIZATION_DENY;
		if ($user->getId() != $monograph->getUserId()) return AUTHORIZATION_DENY;

		// Made it through -- permit access.
		return AUTHORIZATION_PERMIT;
	}
}

?>
