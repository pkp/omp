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

import('classes.workflow.UserGroupStageAssignment');

class UserGroupStageAssignmentDAO extends DAO {

	function &getUserGroupsByStage($pressId, $stageId) {
		$result =& $this->retrieve('
				SELECT ug.*
				FROM user_groups ug JOIN user_group_stage ugs ON ug.user_group_id = ugs.user_group_id AND ug.press_id = ugs.press_id
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

	function &newDataObject() {
		$userGroupStageAssignment =& new UserGroupStageAssignment();
		return $userGroupStageAssignment;
	}

	function &_returnFromRow(&$row) {
		$userGroupStageAssignment =& $this->newDataObject();
		$userGroupStageAssignment->setPressId($row['press_id']);
		$userGroupStageAssignment->setUserGroupId($row['user_group_id']);
		$userGroupStageAssignment->setStageId($row['stage_id']);
		return $userGroupStageAssignment;
	}
}

?>
