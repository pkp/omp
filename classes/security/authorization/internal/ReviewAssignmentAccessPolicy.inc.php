<?php
/**
 * @file classes/security/authorization/internal/ReviewAssignmentAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignmentAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a monograph based on whether the user is an assigned reviewer.
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class ReviewAssignmentAccessPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function ReviewAssignmentAccessPolicy(&$request) {
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
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getLastReviewRoundReviewAssignmentByReviewer($monograph->getId(), $user->getId());

		if (is_a($reviewAssignment, 'ReviewAssignment')) {
			// Save the review assignment to the authorization context.
			$this->addAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT, $reviewAssignment);
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
