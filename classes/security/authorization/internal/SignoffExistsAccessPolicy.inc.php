<?php
/**
 * @file classes/security/authorization/internal/SignoffExistsAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffExistsAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a signoff for the current press
 *
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class SignoffExistsAccessPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function SignoffExistsAccessPolicy(&$request) {
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
		$baseSignoff =& $signoff;

		// Check that the signoff exists
		if (!is_a($signoff, 'Signoff')) return AUTHORIZATION_DENY;

		// Check that we know what the current press is
		$press =& $this->_request->getPress();
		if (!is_a($press, 'Press')) return AUTHORIZATION_DENY;

		// Ensure that the signoff belongs to the current press
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$monographFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		while (true) switch ($signoff->getAssocType()) {
			case ASSOC_TYPE_SIGNOFF:
				// This signoff is attached to another signoff.
				// We need to determine that the attached
				// signoff belongs to the current press.
				$newSignoff =& $signoffDao->getById($signoff->getAssocId());
				if (!is_a($newSignoff, 'Signoff')) return AUTHORIZATION_DENY;

				// Flip the reference so that the new object
				// gets authorized.
				unset($signoff);
				$signoff =& $newSignoff;
				unset($newSignoff);
				break;
			case ASSOC_TYPE_MONOGRAPH_FILE:
				// Get the monograph file
				$monographFile =& $monographFileDao->getLatestRevision($signoff->getAssocId());
				if (!is_a($monographFile, 'MonographFile')) return AUTHORIZATION_DENY;

				// Get the monograph
				$monograph =& $monographDao->getById($monographFile->getSubmissionId(), $press->getId());
				if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

				// Integrity checks OK. Permit.
				$this->addAuthorizedContextObject(ASSOC_TYPE_SIGNOFF, $baseSignoff);
				return AUTHORIZATION_PERMIT;
			case ASSOC_TYPE_MONOGRAPH:
				$monograph =& $monographDao->getById($signoff->getAssocId());
				if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

				if ($monograph->getPressId() != $press->getId()) return AUTHORIZATION_DENY;

				// Checks out OK. Permit.
				$this->addAuthorizedContextObject(ASSOC_TYPE_SIGNOFF, $baseSignoff);
				return AUTHORIZATION_PERMIT;
			default: return AUTHORIZATION_DENY;
		}
	}
}

?>
