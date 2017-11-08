<?php

/**
 * @file classes/security/UserGroupDAO.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupDAO
 * @ingroup security
 * @see UserGroup
 *
 * @brief Operations for retrieving and modifying User Groups and user group
 * assignments in OMP.
 */
import('lib.pkp.classes.security.PKPUserGroupDAO');
import('classes.security.UserGroup');

class UserGroupDAO extends PKPUserGroupDAO {

	/**
	 * @copydoc PKPUserGrouprDAO::newDataObject()
	 */
	function newDataObject() {
		return new UserGroup();
	}

	/**
	 * @copydoc PKPUserGrouprDAO::getAdditionalFieldNames()
	 */
	public function getAdditionalFieldNames() {
		return array_merge(parent::getAdditionalFieldNames(), array(
			'isVolumeEditor',
		));
	}
}
