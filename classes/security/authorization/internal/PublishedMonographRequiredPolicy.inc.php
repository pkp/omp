<?php
/**
 * @file classes/security/authorization/internal/PublishedMonographRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonographRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid published monograph.
 */

import('lib.pkp.classes.security.authorization.DataObjectRequiredPolicy');

class PublishedMonographRequiredPolicy extends DataObjectRequiredPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $submissionParameterName string the request parameter we expect
	 *  the submission id in.
	 */
	function PublishedMonographRequiredPolicy(&$request, &$args, $submissionParameterName = 'monographId', $operations = null) {
		parent::DataObjectRequiredPolicy($request, $args, $submissionParameterName, 'user.authorization.invalidMonograph', $operations);
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see DataObjectRequiredPolicy::dataObjectEffect()
	 */
	function dataObjectEffect() {
		// Get the monograph id.
		$monographId = $this->getDataObjectId();
		if ($monographId === false) return AUTHORIZATION_DENY;

		// Validate the monograph ID.
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monographId);
		if (!is_a($publishedMonograph, 'PublishedMonograph')) return AUTHORIZATION_DENY;

		// Validate that this published monograph belongs to the
		// current press.
		$press =& $this->_request->getPress();
		if ($press->getId() !== $publishedMonograph->getPressId()) return AUTHORIZATION_DENY;

		// Save the published monograph to the authorization context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH, $publishedMonograph);
		return AUTHORIZATION_PERMIT;
	}
}

?>
