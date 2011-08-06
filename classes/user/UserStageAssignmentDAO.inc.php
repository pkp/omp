<?php

/**
 * @file classes/user/UserStageAssignmentDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserStageAssignmentDAO
 * @ingroup user
 * @see User, StageAssignment, and UserDAO
 *
 * @brief Operations for users as related to their stage assignments
 */

import('classes.user.UserDAO');

class UserStageAssignmentDAO extends UserDAO {
	/**
	 * Retrieve a set of users not assigned to a given submission stage as a user group
	 * @param $submissionId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @return object DAOResultFactory
	 */
	function getUsersNotAssignedToStageInUserGroup($submissionId, $stageId, $userGroupId) {
		$params = array((int) $submissionId, (int) $stageId, (int) $userGroupId);

		$result =& $this->retrieve(
			'SELECT u.* FROM users u
				LEFT JOIN user_user_groups uug ON (u.user_id = uug.user_id)
				LEFT JOIN stage_assignments s ON (s.user_id = uug.user_id AND s.user_group_id = uug.user_group_id
												AND s.submission_id = ? AND s.stage_id = ?)
				WHERE uug.user_group_id = ? AND s.user_group_id IS NULL',
			$params);

		$returner = new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
		return $returner;
	}

	/**
	 * Retrieve StageAssignments by submission and stage IDs.
	 * @param $submissionId int
	 * @param $stageId int (optional)
	 * @param $userGroupId int (optional)
	 * @return DAOResultFactory StageAssignment
	 */
	function getUsersBySubmissionAndStageId($submissionId, $stageId = null, $userGroupId = null, $roleId = null) {
		return $this->_getUsersByIds($submissionId, $stageId, $userGroupId, $roleId);
	}

	/**
	 * Delete a stage assignment by Id.
	 * @param  $assignmentId
	 * @return bool
	 */
	function deleteAssignment($assignmentId) {
		return $this->update('DELETE FROM stage_assignments WHERE stage_assignment_id = ?', $assignmentId);
	}


	//
	// Private helper method
	//
	/**
	 * Retrieve a user by submission and stage IDs.
	 * Private method because it serves two purposes: returns a single assignment
	 * or returns a factory, depending on the calling context.
	 * @param $submissionId int
	 * @param $stageId int optional
	 * @param $userGroupId int optional
	 * @param $userId int optional
	 * @param $roleId int optional
	 * @return object DAOResultFactory
	 */
	function _getUsersByIds($submissionId, $stageId = null, $userGroupId = null, $userId = null, $roleId = null) {
		$params = array((int) $submissionId);
		if (isset($stageId)) $params[] = (int) $stageId;
		if (isset($userGroupId)) $params[] = (int) $userGroupId;
		if (isset($userId)) $params[] = (int) $userId;
		if (isset($roleId)) $params[] = (int) $roleId;

		$result =& $this->retrieve(
			'SELECT u.*
			FROM stage_assignments s
			INNER JOIN users u ON (u.user_id = s.user_id) ' .
			(isset($roleId) ? 'INNER JOIN user_groups ug ON (ug.user_group_id = s.user_group_id) ' : '') .
			'WHERE submission_id = ?' .
			(isset($stageId) ? ' AND stage_id = ?' : '') .
			(isset($userGroupId) ? ' AND user_group_id = ?':'') .
			(isset($userId)?' AND u.user_id = ? ' : '') .
			(isset($roleId)?' AND ug.role_id = ?' : ''),
			$params);

		$returner = null;
		// This is a little obscure.
		// 4 params and 1 search results, means calling context was seeking an individual user.
		if ($result->RecordCount() == 1 && count($params) == 4) {
			// If all parameters were specified, then seeking only one assignment.
			$returner =& $this->_returnUserFromRowWithData($result->GetRowAssoc(false));
		} elseif ($result) {
			$returner = new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
		}
		return $returner;
	}
}

?>
