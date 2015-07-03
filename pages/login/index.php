<?php

/**
 * @defgroup pages_login Login page
 */

/**
 * @file pages/login/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Handle login/logout requests.
 *
 * @ingroup pages_login
 */

switch ($op) {
	case 'signInAsUser':
	case 'signOutAsUser':
	case 'index':
	case 'signIn':
	case 'signOut':
	case 'lostPassword':
	case 'requestResetPassword':
	case 'resetPassword':
	case 'changePassword':
	case 'savePassword':
		define('HANDLER_CLASS', 'LoginHandler');
		import('pages.login.LoginHandler');
		break;
}

?>
