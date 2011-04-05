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


define('WORKFLOW_STAGE_PATH_SUBMISSION', 'submission');
define('WORKFLOW_STAGE_PATH_INTERNAL_REVIEW', 'internalReview');
define('WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW', 'externalReview');
define('WORKFLOW_STAGE_PATH_EDITING', 'editing');
define('WORKFLOW_STAGE_PATH_PRODUCTION', 'production');

import('lib.pkp.classes.security.PKPUserGroupDAO');

class UserGroupDAO extends PKPUserGroupDAO {
	/**
	 * Constructor.
	 */
	function UserGroupDAO() {
		parent::PKPUserGroupDAO();
	}

	function &getUserGroupsByStage($pressId, $stageId) {
		$result =& $this->retrieve(
			'SELECT	ug.*
			FROM	user_groups ug
				JOIN user_group_stage ugs ON ug.user_group_id = ugs.user_group_id AND ug.context_id = ugs.press_id
			WHERE	ugs.press_id = ? AND
				ugs.stage_id = ?',
			array((int) $pressId, (int) $stageId)
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

	function assignGroupToStage($pressId, $userGroupId, $stageId) {
		return $this->update(
			'INSERT INTO user_group_stage (press_id, user_group_id, stage_id) VALUES (?, ?, ?)',
			array((int) $pressId, (int) $userGroupId, (int) $stageId)
		);
	}

	function removeGroupFromStage($pressId, $userGroupId, $stageId) {
		return $this->update(
			'DELETE FROM user_group_stage WHERE press_id = ? AND user_group_id = ? AND stage_id = ?',
			array((int) $pressId, (int) $userGroupId, (int) $stageId)
		);
	}

	function assignmentExists($pressId, $userGroupId, $stageId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM user_group_stage WHERE press_id = ? AND user_group_id = ? AND stage_id = ?',
			array((int) $pressId, (int) $userGroupId, (int) $stageId)
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
