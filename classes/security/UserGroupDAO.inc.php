<?php

/**
 * @file classes/security/UserGroupDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	 * @param  $pressId
	 * @param  $stageId
	 * @return DAOResultFactory
	 */
	function &getUserGroupsByStage($pressId, $stageId, $omitAuthors = false, $omitReviewers = false) {
		$params = array((int) $pressId, (int) $stageId);
		if ($omitAuthors) $params[] = ROLE_ID_AUTHOR;
		if ($omitReviewers) $params[] = ROLE_ID_REVIEWER;
		$result =& $this->retrieve(
			'SELECT	ug.*
			FROM	user_groups ug
				JOIN user_group_stage ugs ON (ug.user_group_id = ugs.user_group_id AND ug.context_id = ugs.press_id)
			WHERE	ugs.press_id = ? AND
				ugs.stage_id = ?' .
				($omitAuthors?' AND ug.role_id <> ?':'') .
				($omitReviewers?' AND ug.role_id <> ?':'') .
			' ORDER BY role_id ASC',
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_returnFromRow');
		return $returner;
	}

	/**
	 * Get all stages assigned to one user group in one context.
	 * @param Integer $pressId The user group context.
	 * @param Integer $userGroupId
	 */
	function getAssignedStagesByUserGroupId($pressId, $userGroupId) {
		$result =& $this->retrieve(
			'SELECT	stage_id
			FROM	user_group_stage
			WHERE	press_id = ? AND
				user_group_id = ?',
			array((int) $pressId, (int) $userGroupId)
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
		$result = $this->retrieve('SELECT COUNT(*)
									FROM user_group_stage
									WHERE user_group_id = ? AND stage_id = ?',
								array((int) $userGroupId, (int) $stageId));

		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Assign a user group to a stage
	 * @param $pressId int
	 * @param $userGroupId int
	 * @param $stageId int
	 * @return bool
	 */
	function assignGroupToStage($pressId, $userGroupId, $stageId) {
		return $this->update(
			'INSERT INTO user_group_stage (press_id, user_group_id, stage_id) VALUES (?, ?, ?)',
			array((int) $pressId, (int) $userGroupId, (int) $stageId)
		);
	}

	/**
	 * Remove a user group from a stage
	 * @param $pressId int
	 * @param $userGroupId int
	 * @param $stageId int
	 * @return bool
	 */
	function removeGroupFromStage($pressId, $userGroupId, $stageId) {
		return $this->update(
			'DELETE FROM user_group_stage WHERE press_id = ? AND user_group_id = ? AND stage_id = ?',
			array((int) $pressId, (int) $userGroupId, (int) $stageId)
		);
	}

	/**
	 * Check to see whether a user is assigned to a stage ID via a user group.
	 * @param $pressId int
	 * @param $userId int
	 * @param $staeId int
	 * @return boolean
	 */
	function userAssignmentExists($pressId, $userId, $stageId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM user_group_stage ugs, user_user_groups uug WHERE ugs.user_group_id = uug.user_group_id AND ugs.press_id = ? AND uug.user_id = ? AND ugs.stage_id = ?',
			array((int) $pressId, (int) $userId, (int) $stageId)
		);

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

	/**
	 * Convert a stage id into a stage translation key
	 * @param $stageId integer
	 * @return string|null
	 */
	function getTranslationKeyFromId($stageId) {
		$stageMapping = $this->getWorkflowStageTranslationKeys();

		assert(isset($stageMapping[$stageId]));
		return $stageMapping[$stageId];
	}

	/**
	 * Return a mapping of workflow stages and its translation keys.
	 * NB: PHP4 work-around for a private static class member
	 * @return array
	 */
	function getWorkflowStageTranslationKeys() {
		static $stageMapping = array(
			WORKFLOW_STAGE_ID_SUBMISSION => 'submission.submission',
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW => 'workflow.review.internalReview',
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => 'workflow.review.externalReview',
			WORKFLOW_STAGE_ID_EDITING => 'submission.editorial',
			WORKFLOW_STAGE_ID_PRODUCTION => 'submission.production'
		);

		return $stageMapping;
	}
}

?>
