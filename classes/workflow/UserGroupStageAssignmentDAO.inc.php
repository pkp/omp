<?php

/**
 * @file classes/workflow/UserGroupStageAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupStageAssignmentDAO
 * @ingroup workflow
 *
 * @brief Class for managing user group to publication stage assignments
 */


define('WORKFLOW_STAGE_PATH_SUBMISSION', 'submission');
define('WORKFLOW_STAGE_PATH_INTERNAL_REVIEW', 'internalReview');
define('WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW', 'externalReview');
define('WORKFLOW_STAGE_PATH_EDITING', 'editing');
define('WORKFLOW_STAGE_PATH_PRODUCTION', 'production');

class UserGroupStageAssignmentDAO extends DAO {
	function &getUserGroupsByStage($pressId, $stageId) {
		$result =& $this->retrieve('
				SELECT ug.*
				FROM user_groups ug JOIN user_group_stage ugs ON ug.user_group_id = ugs.user_group_id AND ug.context_id = ugs.press_id
				WHERE ugs.press_id = ? AND ugs.stage_id = ?',
				array($pressId, $stageId));

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$returner =& new DAOResultFactory($result, $userGroupDao, '_returnFromRow');
		return $returner;
	}

	function assignGroupToStage($pressId, $userGroupId, $stageId) {
		return $this->update('INSERT INTO user_group_stage
							SET press_id = ?, user_group_id = ?, stage_id = ?',
							array($pressId, $userGroupId, $stageId));
	}

	function removeGroupFromStage($pressId, $userGroupId, $stageId) {
		return $this->update('DELETE FROM user_group_stage
							WHERE press_id = ? AND user_group_id = ? AND stage_id = ?',
							array($pressId, $userGroupId, $stageId));
	}

	function assignmentExists($pressId, $userGroupId, $stageId) {
		$result =& $this->retrieve('SELECT COUNT(*) FROM user_group_stage
									WHERE press_id = ? AND user_group_id = ? AND stage_id = ?',
							array($pressId, $userGroupId, $stageId));

		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
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
