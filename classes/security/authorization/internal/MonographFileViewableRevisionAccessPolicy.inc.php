<?php
/**
 * @file classes/security/authorization/internal/MonographFileViewableRevisionAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileViewableRevisionAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Monograph file policy to check if the current user can access a revision file.
 *
 */

import('classes.security.authorization.internal.MonographFileBaseAccessPolicy');

class MonographFileViewableRevisionAccessPolicy extends MonographFileBaseAccessPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileViewableRevisionAccessPolicy(&$request) {
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

		// Get the monograph file.
		$monographFile =& $this->getMonographFile($request);
		if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

		// Make sure that it's in the review revision stage
		if ($monographFile->getFileStage() != MONOGRAPH_FILE_REVIEW_REVISION) return AUTHORIZATION_DENY;

		// Make sure the file belongs to the monograph in request.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;
		if ($monograph->getId() != $monographFile->getSubmissionId()) return AUTHORIZATION_DENY;

		// Make sure the file is visible.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->getByMonographFileId($monographFile->getFileId());
		import('classes.workflow.EditorDecisionActionsManager');
		if(!EditorDecisionActionsManager::getEditorTakenActionInReviewRound($reviewRound)) {
			return AUTHORIZATION_DENY;
		}

		// Made it through -- permit access.
		return AUTHORIZATION_PERMIT;
	}
}

?>
