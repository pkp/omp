<?php

/**
 * @file classes/user/UserGroupStageAssignment.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupStageAssignment
 * @ingroup workflow
 * @see UserGroupStageAssignmentDAO
 *
 * @brief Basic class describing user group to publication stage assignments
 */

define('PUBLICATION_STAGE_ID_SUBMISSION',		1);
define('PUBLICATION_STAGE_ID_INTERNAL_REVIEW',  2);
define('PUBLICATION_STAGE_ID_EXTERNAL_REVIEW', 3);
define('PUBLICATION_STAGE_ID_EDITING', 4);
define('PUBLICATION_STAGE_ID_PRODUCTION', 5);

class UserGroupStageAssignment extends DataObject {

	/**
	 * Set the press id
	 * @param $pressId int
	 */
	function setPressId(&$pressId) {
	 $this->setData('pressId', $pressId);
	}

	/**
	 * Get the press id
	 * @return int
	 */
	function &getPressId() {
	 return $this->getData('pressId');
	}

	/**
	 * Set the the user group id
	 * @param $userGroupId int
	 */
	function setUserGroupId(&$userGroupId) {
	 $this->setData('userGroupId', $userGroupId);
	}

	/**
	 * Get the the user group id
	 * @return int
	 */
	function &getUserGroupId() {
	 return $this->getData('userGroupId');
	}

	/**
	 * Set the publication stage id
	 * @param $stageId int
	 */
	function setStageId(&$stageId) {
	 $this->setData('stageId', $stageId);
	}

	/**
	 * Get the publication stage id
	 * @return int
	 */
	function &getStageId() {
	 return $this->getData('stageId');
	}
}

?>
