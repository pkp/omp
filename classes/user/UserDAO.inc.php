<?php

/**
 * @file classes/user/UserDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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

	/**
	 * Create a new data object
	 * @return User
	 */
	function newDataObject() {
		return new User();
	}
}

?>
