<?php
/**
 * @file classes/security/authorization/internal/ReviewerSubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a monograph based on whether the user is an assigned reviewer.
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 *
 * FIXME: We might make this policy aware of all kinds of submissions
 * so that we can use it cross-app. Just rename it to SubmissionAuthorPolicy
 * and insert the remaining ASSOC_TYPEs below in the code.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class ReviewerSubmissionAccessPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function ReviewerSubmissionAccessPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.monographReviewer');
		$this->_request =& $request;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the user
		$user =& $this->_request->getUser();
		if (!is_a($user, 'PKPUser')) return AUTHORIZATION_DENY;

		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Check if a review assignment exists between the submission and the user
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($monograph->getId(), $user->getId(), $monograph->getCurrentRound());

		if (is_a($reviewAssignment, 'ReviewAssignment')) {
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
