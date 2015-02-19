<?php
/**
 * @file classes/handler/validation/HandlerValidatorPress.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 *
 * NB: Deprecated - please use ContextRequiredPolicy instead, see #5868.
 */

import('lib.pkp.classes.handler.validation.HandlerValidatorPolicy');
import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');

class HandlerValidatorPress extends HandlerValidatorPolicy {
	/**
	 * Constructor.
	 * @see HandlerValidator::HandlerValidator()
	 */
	function HandlerValidatorPress(&$handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
		$application = PKPApplication::getApplication();
		$request = $application->getRequest();
		$policy = new ContextRequiredPolicy($request, $message);
		parent::HandlerValidatorPolicy($policy, $handler, $redirectToLogin, $message, $additionalArgs);
	}
}

?>
