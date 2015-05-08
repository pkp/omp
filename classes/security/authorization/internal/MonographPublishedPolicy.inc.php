<?php
/**
 * @file classes/security/authorization/internal/MonographPublishedPolicy.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographPublishedPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to control access to a monograph base on its publication.
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 *
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class MonographPublishedPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographPublishedPolicy($request) {
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
		// Get the monograph
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Get the published monograph; store as authorized
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId());
		if (!is_a($publishedMonograph, 'PublishedMonograph')) return AUTHORIZATION_DENY;
		$this->addAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH, $publishedMonograph);

		return AUTHORIZATION_PERMIT;
	}
}

?>
