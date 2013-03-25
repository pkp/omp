<?php
/**
 * @file classes/security/authorization/internal/MonographFileSubmissionStageRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileSubmissionStageRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to ensure that we have a file at submission stage.
 *
 */

import('lib.pkp.classes.security.authorization.internal.SubmissionFileBaseAccessPolicy');

class MonographFileSubmissionStageRequiredPolicy extends SubmissionFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileSubmissionStageRequiredPolicy(&$request, $fileIdAndRevision = null) {
		parent::SubmissionFileBaseAccessPolicy($request, $fileIdAndRevision);
	}


	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		$request =& $this->getRequest();

		// Get the monograph file.
		$monographFile =& $this->getSubmissionFile($request);
		if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

		// Make sure that it's in the submission stage
		if ($monographFile->getFileStage() != SUBMISSION_FILE_SUBMISSION) return AUTHORIZATION_DENY;

		// Make sure the file belongs to the monograph in request.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;
		if ($monograph->getId() != $monographFile->getSubmissionId()) return AUTHORIZATION_DENY;

		// Made it through -- permit access.
		return AUTHORIZATION_PERMIT;
	}
}

?>
