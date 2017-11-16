<?php

/**
 * @file classes/security/PKPUserGroup.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPUserGroup
 * @ingroup security
 * @see PKPUserGroupDAO
 *
 * @brief Describes user groups
 */

// Bring in role constants.
import('lib.pkp.classes.security.PKPUserGroup');

class UserGroup extends PKPUserGroup {

	/**
	 * Get whether or not this user role should be displayed as a volume editor
	 *
	 * @return boolean
	 */
	public function getIsVolumeEditor() {
		return $this->getData('isVolumeEditor');
	}

	/**
	 * Set whether or not this user role should be displayed as a volume editor
	 *
	 * @param boolean $isVolumeEditor
	 */
	public function setIsVolumeEditor($isVolumeEditor) {
		$this->setData('isVolumeEditor', $isVolumeEditor);
	}
}
