<?php

/**
 * @file classes/stageAssignment/StageAssignmentDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageAssignmentDAO
 * @ingroup stageAssignment
 * @see StageAssignment
 *
 * @brief Operations for retrieving and modifying StageAssignment objects.
 */

import('classes.stageAssignment.StageAssignment');

class StageAssignmentDAO extends DAO {
	/**
	 * Constructor
	 */
	function StageAssignmentDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve an assignment by  its ID
	 * @param $stageAssignmentId int
	 * @return StageAssignment
	 */
	function getById($stageAssignmentId) {
		$result =& $this->retrieve(
			'SELECT * FROM stage_assignments WHERE stage_assignment_id = ?',
			(int) $stageAssignmentId
		);
		return $this->_fromRow($result->GetRowAssoc(false));
	}

	/**
	 * Retrieve StageAssignments by submission and stage IDs.
	 * @param $submissionId int
	 * @param $stageId int (optional)
	 * @param $userGroupId int (optional)
	 * @param $userId int (optional)
	 * @return DAOResultFactory StageAssignment
	 */
	function getBySubmissionAndStageId($submissionId, $stageId = null, $userGroupId = null, $userId = null) {
		return $this->_getByIds($submissionId, $stageId, $userGroupId, $userId);
	}

	/**
	 * Retrieve StageAssignments by submission and role IDs.
	 * @param $submissionId int
	 * @param $roleId int
	 * @param $stageId int (optional)
	 * @param $userId int (optional)
	 * @return DAOResultFactory StageAssignment
	 */
	function getBySubmissionAndRoleId($submissionId, $roleId, $stageId = null, $userId = null) {
		return $this->_getByIds($submissionId, $stageId, null, $userId, $roleId);
	}

	/**
	 * @param $userId int
	 * @return StageAssignment
	 */
	function getByUserId($userId) {
		return $this->_getByIds(null, null, null, $userId);
	}

	/**
	 * Test if an editor or a series editor is assigned to the submission
	 * This test is used to determine what grid to place a submission into,
	 * and to know if the review stage can be started.
	 * @param $submissionId (int) The id of the submission being tested.
	 * @param $stageId (int) The id of the stage being tested.
	 * @return bool
	 */
	function editorAssignedToStage($submissionId, $stageId) {
		$result =& $this->retrieve(
			'SELECT	COUNT(*)
			FROM	stage_assignments sa
				JOIN user_groups ug ON (sa.user_group_id = ug.user_group_id)
				JOIN user_group_stage ugs ON (ug.user_group_id = ugs.user_group_id)
			WHERE	sa.submission_id = ? AND
				ugs.stage_id = ? AND
				ug.role_id IN (?, ?)',
			array((int) $submissionId, (int) $stageId, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Fetch a stageAssignment by symbolic info, building it if needed.
	 * @param $submissionId int
	 * @param $userGroupId int
	 * @param $userId int
	 * @return StageAssignment
	 */
	function build($submissionId, $userGroupId, $userId) {

		// If one exists, fetch and return.
		$stageAssignment =& $this->getBySubmissionAndStageId($submissionId, null, $userGroupId, $userId);
		if (!$stageAssignment->wasEmpty()) return $stageAssignment;

		// Otherwise, build one.
		unset($stageAssignment);
		$stageAssignment = $this->newDataObject();
		$stageAssignment->setSubmissionId($submissionId);
		$stageAssignment->setUserGroupId($userGroupId);
		$stageAssignment->setUserId($userId);
		$this->insertObject($stageAssignment);
		return $stageAssignment;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return StageAssignmentEntry
	 */
	function newDataObject() {
		return new StageAssignment();
	}

	/**
	 * Internal function to return an StageAssignment object from a row.
	 * @param $row array
	 * @return StageAssignment
	 */
	function _fromRow(&$row) {
		$stageAssignment = $this->newDataObject();

		$stageAssignment->setId($row['stage_assignment_id']);
		$stageAssignment->setSubmissionId($row['submission_id']);
		$stageAssignment->setUserId($row['user_id']);
		$stageAssignment->setUserGroupId($row['user_group_id']);
		$stageAssignment->setDateAssigned($row['date_assigned']);

		return $stageAssignment;
	}

	/**
	 * Insert a new StageAssignment.
	 * @param $stageAssignment StageAssignment
	 * @return bool
	 */
	function insertObject(&$stageAssignment) {
		return $this->update(
			sprintf(
				'INSERT INTO stage_assignments
					(submission_id, user_group_id, user_id, date_assigned)
				VALUES
					(?, ?, ?, %s)',
				$this->datetimeToDB(Core::getCurrentDate())
			),
			array(
				$stageAssignment->getSubmissionId(),
				$this->nullOrInt($stageAssignment->getUserGroupId()),
				$this->nullOrInt($stageAssignment->getUserId())
			)
		);
	}

	/**
	 * Delete a StageAssignment.
	 * @param $stageAssignment StageAssignment
	 * @return int
	 */
	function deleteObject($stageAssignment) {
		return $this->deleteByAll(
			$stageAssignment->getSubmissionId(),
			$stageAssignment->getUserGroupId(),
			$stageAssignment->getUserId()
		);
	}

	/**
	 * Delete a stageAssignment by matching on all fields.
	 * @param $submissionId int
	 * @param $userGroupId int
	 * @param $userId int
	 * @return boolean
	 */
	function deleteByAll($submissionId, $userGroupId, $userId) {
		return $this->update(
			'DELETE FROM stage_assignments
			WHERE	submission_id = ?
				AND user_group_id = ?
				AND user_id = ?',
			array((int) $submissionId, (int) $userGroupId, (int) $userId)
		);
	}

	/**
	 * Retrieve a stageAssignment by submission and stage IDs.
	 * Private method that holds most of the work.
	 * serves two purposes: returns a single assignment or returns a factory,
	 * depending on the calling context.
	 * @param $submissionId int
	 * @param $stageId int optional
	 * @param $userGroupId int optional
	 * @param $userId int optional
	 * @param $single bool specify if only one stage assignment (default is a ResultFactory)
	 * @return StageAssignment or ResultFactory
	 */
	function _getByIds($submissionId = null, $stageId = null, $userGroupId = null, $userId = null, $roleId = null, $single = false) {
		$conditions = array();
		$params = array();
		if (isset($submissionId)) {
			$conditions[] = 'sa.submission_id = ?';
			$params[] = (int) $submissionId;
		}
		if (isset($stageId)) {
			$conditions[] = 'ugs.stage_id = ?';
			$params[] = (int) $stageId;
		}
		if (isset($userGroupId)) {
			$conditions[] = 'sa.user_group_id = ?';
			$params[] = (int) $userGroupId;
		}
		if (isset($userId)) {
			$conditions[] = 'sa.user_id = ?';
			$params[] = (int) $userId;
		}

		if (isset($roleId)) {
			$conditions[] = 'ug.role_id = ?';
			$params[] = (int) $roleId;
		}

		$result =& $this->retrieve(
			'SELECT sa.* FROM stage_assignments sa ' .
			(isset($stageId)? 'JOIN user_group_stage ugs ON sa.user_group_id = ugs.user_group_id ':'') .
			(isset($roleId)?' LEFT JOIN user_groups ug ON sa.user_group_id = ug.user_group_id ':'') .
			'WHERE ' . (implode(' AND ', $conditions)),
			$params
		);

		$returner = null;
		if ( $single ) {
				// all four parameters must be specified for a single record to be returned
				if (count($params) !== 4) return false;
				// no matches were found.
				if ($result->RecordCount() == 0) return false;
				$returner =& $this->_fromRow($result->GetRowAssoc(false));
				$result->Close();
		} else {
			// In any other case, return a list of all assignments
			$returner = new DAOResultFactory($result, $this, '_fromRow');
		}
		return $returner;
	}
}

?>
