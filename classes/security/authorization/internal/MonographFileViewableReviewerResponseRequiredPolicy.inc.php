<?php
/**
 * @file classes/security/authorization/internal/MonographFileViewableReviewerResponseRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileViewableReviewerResponseRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to ensure that we have a viewable
 * reviewer response file.
 *
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileViewableReviewerResponseRequiredPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileViewableReviewerResponseRequiredPolicy(&$request) {
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

		// Make sure the file belongs to the monograph in request.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;
		if ($monograph->getId() != $monographFile->getSubmissionId()) return AUTHORIZATION_DENY;

		// Make sure the file is visible.
		if (!$monographFile->getViewable()) return AUTHORIZATION_DENY;

		// Made it through -- permit access.
		return AUTHORIZATION_PERMIT;
	}
}

?>
