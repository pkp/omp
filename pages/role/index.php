<?php

/**
 * @defgroup pages_role
 */
 
/**
 * @file pages/role/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_role
 * @brief Handle custom role requests. 
 *
 */

// $Id$


switch ($op) {
	case 'index':
		define('HANDLER_CLASS', 'RoleHandler');
		import('pages.role.RoleHandler');
		break;
}

?>
