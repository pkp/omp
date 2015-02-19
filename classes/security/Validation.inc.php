<?php

/**
 * @file classes/security/Validation.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Validation
 * @ingroup security
 *
 * @brief Class providing user validation/authentication operations.
 */


import('lib.pkp.classes.security.PKPValidation');
import('lib.pkp.classes.security.UserGroup');

class Validation extends PKPValidation {

	/**
	 * Shortcut for checking authorization as press manager.
	 * @param $pressId int
	 * @return boolean
	 */
	static function isPressManager($pressId = -1) {
		return Validation::isAuthorized(ROLE_ID_MANAGER, $pressId);
	}

	/**
	 * Shortcut for checking authorization as series editor.
	 * @param $pressId int
	 * @return boolean
	 */
	static function isSeriesEditor($pressId = -1) {
		return Validation::isAuthorized(ROLE_ID_SUB_EDITOR, $pressId);
	}
}

?>
