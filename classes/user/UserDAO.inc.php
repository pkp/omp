<?php

/**
 * @file classes/user/UserDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserDAO
 * @ingroup user
 * @see PKPUserDAO
 *
 * @brief Basic class describing users existing in the system.
 */

// $Id$


import('classes.user.User');
import('lib.pkp.classes.user.PKPUserDAO');

class UserDAO extends PKPUserDAO {
	/**
	 * Retrieve an array of users with no role defined.
	 * @param $allowDisabled boolean
	 * @param $dbResultRange object The desired range of results to return
	 * @return array matching Users
	 */
	function &getUsersWithNoUserGroupAssignments($allowDisabled = true, $dbResultRange = null) {
		$sql = 'SELECT u.* FROM users u LEFT JOIN user_user_groups uug ON u.user_id=uug.user_id WHERE uug.user_group_id IS NULL';

		$orderSql = ' ORDER BY u.last_name, u.first_name'; // FIXME Add "sort field" parameter?

		$result =& $this->retrieveRange($sql . ($allowDisabled?'':' AND u.disabled = 0') . $orderSql, false, $dbResultRange);

		$returner = new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
		return $returner;
	}

}

?>
