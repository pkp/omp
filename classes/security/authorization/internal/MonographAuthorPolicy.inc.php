<?php
/**
 * @file classes/security/authorization/internal/MonographAuthorPolicy.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographAuthorPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a monograph base on authorship.
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 *
 * FIXME: We might make this policy aware of all kinds of submissions
 * so that we can use it cross-app. Just rename it to SubmissionAuthorPolicy
 * and insert the remaining ASSOC_TYPEs below in the code.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class MonographAuthorPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographAuthorPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.monographAuthor');
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

		// Check authorship of the monograph.
		if ($monograph->getUserId() === $user->getId()) {
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
