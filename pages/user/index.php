<?php

/**
 * @defgroup pages_user User page
 */

/**
 * @file pages/user/index.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_user
 * @brief Handle requests for user functions.
 *
 */


switch ($op) {
	//
	// Profiles
	//
	case 'profile':
		import('lib.pkp.pages.user.ProfileHandler');
		define('HANDLER_CLASS', 'ProfileHandler');
		break;
	//
	// Registration
	//
	case 'register':
	case 'registerUser':
	case 'registrationComplete':
	case 'activateUser':
		import('lib.pkp.pages.user.RegistrationHandler');
		define('HANDLER_CLASS', 'RegistrationHandler');
		break;
	//
	// Default handler
	//
	case 'index':
	case 'setLocale':
	case 'authorizationDenied':
	case 'getInterests':
	case 'toggleHelp':
		define('HANDLER_CLASS', 'UserHandler');
		import('pages.user.UserHandler');
		break;
}

?>
