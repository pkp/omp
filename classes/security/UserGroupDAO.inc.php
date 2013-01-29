<?php

/**
 * @file classes/security/UserGroupDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupDAO
 * @ingroup security
 * @see PKPUserGroupDAO
 *
 * @brief Operations for retrieving and modifying User Groups and user group assignments
 * FIXME: Some of the context-specific features of this class will have
 * to be changed for zero- or double-context applications when user groups
 * are ported over to them.
 */

import('lib.pkp.classes.security.PKPUserGroupDAO');

class UserGroupDAO extends PKPUserGroupDAO {
	/**
	 * Constructor.
	 */
	function UserGroupDAO() {
		parent::PKPUserGroupDAO();
	}

	/**
	 * Get the user groups assigned to each stage. Provide the ability to omit authors and reviewers
	 * Since these are typically stored differently and displayed in different circumstances
	 * @param  $contextId
	 * @param  $stageId
	 * @return DAOResultFactory
	 */
	function &getUserGroupsByStage($contextId, $stageId, $omitAuthors = false, $omitReviewers = false, $roleId = null) {
		$params = array((int) $contextId, (int) $stageId);
		if ($omitAuthors) $params[] = ROLE_ID_AUTHOR;
		if ($omitReviewers) $params[] = ROLE_ID_REVIEWER;
		if ($roleId) $params[] = $roleId;
		$result =& $this->retrieve(
			'SELECT	ug.*
			FROM	user_groups ug
				JOIN user_group_stage ugs ON (ug.user_group_id = ugs.user_group_id AND ug.context_id = ugs.context_id)
			WHERE	ugs.context_id = ? AND
				ugs.stage_id = ?' .
				($omitAuthors?' AND ug.role_id <> ?':'') .
				($omitReviewers?' AND ug.role_id <> ?':'') .
				($roleId?' AND ug.role_id = ?':'') .
			' ORDER BY role_id ASC',
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_returnFromRow');
		return $returner;
	}

	/**
	 * Get all stages assigned to one user group in one context.
	 * @param Integer $contextId The user group context.
	 * @param Integer $userGroupId
	 */
	function getAssignedStagesByUserGroupId($contextId, $userGroupId) {
		$result =& $this->retrieve(
			'SELECT	stage_id
			FROM	user_group_stage
			WHERE	context_id = ? AND
				user_group_id = ?',
			array((int) $contextId, (int) $userGroupId)
		);

		$returner = array();

		while (!$result->EOF) {
			$stageId = $result->Fields('stage_id');
			$returner[$stageId] = $this->getTranslationKeyFromId($stageId);
			$result->MoveNext();
		}

		return $returner;
	}

	/**
	 * Check if a user group is assigned to a stage
	 * @param int $userGroupId
	 * @param int $stageId
	 * @return bool
	 */
	function userGroupAssignedToStage($userGroupId, $stageId) {
		$result = $this->retrieve(
			'SELECT COUNT(*)
			FROM	user_group_stage
			WHERE	user_group_id = ? AND
			stage_id = ?',
			array((int) $userGroupId, (int) $stageId)
		);

		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check to see whether a user is assigned to a stage ID via a user group.
	 * @param $contextId int
	 * @param $userId int
	 * @param $staeId int
	 * @return boolean
	 */
	function userAssignmentExists($contextId, $userId, $stageId) {
		$result =& $this->retrieve(
			'SELECT	COUNT(*)
			FROM	user_group_stage ugs,
				user_user_groups uug
			WHERE	ugs.user_group_id = uug.user_group_id AND
				ugs.context_id = ? AND
				uug.user_id = ? AND
				ugs.stage_id = ?',
			array((int) $contextId, (int) $userId, (int) $stageId)
		);

		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
