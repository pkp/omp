<?php
/**
 * @file classes/security/authorization/internal/MonographSignoffExistsAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSignoffExistsAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a signoff
 *
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class MonographSignoffExistsAccessPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographSignoffExistsAccessPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.monographSignoff');
		$this->_request =& $request;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Check if the signoff exists
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->_request->getUserVar('signoffId'));

		if (is_a($reviewAssignment, 'ReviewAssignment')) {
			// Save the review assignment to the authorization context.
			$this->addAuthorizedContextObject(ASSOC_TYPE_SIGNOFF, $signoff);
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
