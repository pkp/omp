<?php
/**
 * @file classes/handler/HandlerValidatorPress.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 */

import('lib.pkp.classes.handler.validation.HandlerValidator');

class HandlerValidatorPress extends HandlerValidator {
	/**
	 * Constructor.
	 * @param $handler Handler the associated form
	 * @param $message string the error message for validation failures (i18n key)
	 */
	 
	function HandlerValidatorPress(&$handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
		parent::HandlerValidator($handler, $redirectToLogin, $message, $additionalArgs);
	}

	/**
	 * Check if field value is valid.
	 * Value is valid if it is empty and optional or validated by user-supplied function.
	 * @return boolean
	 */
	function isValid() {
		$press =& Request::getPress();
		return ($press)?true:false;
	}
}

?>
