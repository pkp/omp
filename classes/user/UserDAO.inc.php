<?php

/**
 * @file classes/user/UserDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserDAO
 * @ingroup user
 * @see PKPUserDAO
 *
 * @brief Basic class describing users existing in the system.
 */



import('classes.user.User');
import('lib.pkp.classes.user.PKPUserDAO');

class UserDAO extends PKPUserDAO {
	/**
	 * Constructor
	 */
	function UserDAO() {
		parent::PKPUserDAO();
	}
}

?>
