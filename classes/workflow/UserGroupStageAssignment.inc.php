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

define('WORKFLOW_STAGE_PATH_SUBMISSION', 'submission');
define('WORKFLOW_STAGE_PATH_INTERNAL_REVIEW', 'internalReview');
define('WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW', 'externalReview');
define('WORKFLOW_STAGE_PATH_EDITING', 'editing');
define('WORKFLOW_STAGE_PATH_PRODUCTION', 'production');

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

	//
	// Public helper methods
	//
	/**
	 * Convert a stage id into a stage path
	 * @param $stageId integer
	 * @return string|null
	 */
	function getPathFromId($stageId) {
		static $stageMapping = array(
			WORKFLOW_STAGE_ID_SUBMISSION => WORKFLOW_STAGE_PATH_SUBMISSION,
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW => WORKFLOW_STAGE_PATH_INTERNAL_REVIEW,
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW,
			WORKFLOW_STAGE_ID_EDITING => WORKFLOW_STAGE_PATH_EDITING,
			WORKFLOW_STAGE_ID_PRODUCTION => WORKFLOW_STAGE_PATH_PRODUCTION
		);
		if (isset($stageMapping[$stageId])) {
			return $stageMapping[$stageId];
		} else {
			return null;
		}
	}

	/**
	 * Convert a stage path into a stage id
	 * @param $stagePath string
	 * @return integer|null
	 */
	function getIdFromPath($stagePath) {
		static $stageMapping = array(
			WORKFLOW_STAGE_PATH_SUBMISSION => WORKFLOW_STAGE_ID_SUBMISSION,
			WORKFLOW_STAGE_PATH_INTERNAL_REVIEW => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW => WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
			WORKFLOW_STAGE_PATH_EDITING => WORKFLOW_STAGE_ID_EDITING,
			WORKFLOW_STAGE_PATH_PRODUCTION => WORKFLOW_STAGE_ID_PRODUCTION
		);
		if (isset($stageMapping[$stagePath])) {
			return $stageMapping[$stagePath];
		} else {
			return null;
		}
	}
}

?>
