<?php

/**
 * @file classes/monograph/MonographFileSignoffDAO.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileSignoffDAO
 * @ingroup monograph
 * @see SignoffDAO
 *
 * @brief Extension of SignoffDAO to work with signoffs relating to monograph
 * files.
 */

import('classes.signoff.SignoffDAO');

class MonographFileSignoffDAO extends SignoffDAO {
	/**
	 * Constructor.
	 */
	function MonographFileSignoffDAO() {
		parent::SignoffDAO();
	}


	//
	// Public methods
	//
	/**
	 * @see SignoffDAO::getById
	 */
	function getById($signoffId) {
		return parent::getById($signoffId, ASSOC_TYPE_SUBMISSION_FILE);
	}

	/**
	 * Fetch a signoff by symbolic info, building it if needed.
	 * @param $symbolic string
	 * @param $monographFileId int
	 * @param $userId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @param $fileId int
	 * @param $fileRevision int
	 * @return Signoff
	 */
	function build($symbolic, $monographFileId, $userId = null,
			$userGroupId = null, $fileId = null, $fileRevision = null) {
		return parent::build(
			$symbolic,
			ASSOC_TYPE_SUBMISSION_FILE, $monographFileId,
			$userId, $userGroupId,
			$fileId, $fileRevision
		);
	}

	/**
	 * Determine if a signoff exists
	 * @param string $symbolic
	 * @param int $monographFileId
	 * @param int $stageId
	 * @param int $userGroupId
	 * @return boolean
	 */
	function signoffExists($symbolic, $monographFileId, $userId = null, $userGroupId = null) {
		return parent::signoffExists($symbolic, ASSOC_TYPE_SUBMISSION_FILE, $userId, $userGroupId);
	}

	/**
	 * @see SignoffDAO::newDataObject
	 */
	function newDataObject() {
		$signoff = parent::newDataObject();
		$signoff->setAssocType(ASSOC_TYPE_SUBMISSION_FILE);
		return $signoff;
	}

	/**
	 * Retrieve the first signoff matching the specified symbolic name and
	 * monograph file info.
	 * @param $symbolic string
	 * @param $monographFileId int
	 * @param $userId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @param $fileId int
	 * @param $fileRevision int
	 * @return Signoff
	 */
	function getBySymbolic($symbolic, $monographFileId, $userId = null,
			$userGroupId = null, $fileId = null, $fileRevision = null) {
		return parent::getBySymbolic(
			$symbolic,
			ASSOC_TYPE_SUBMISSION_FILE, $monographFileId,
			$userId, $userGroupId,
			$fileId, $fileRevision
		);
	}

	/**
	 * Retrieve all signoffs matching the specified input parameters
	 * @param $symbolic string
	 * @param $monographFileId int
	 * @param $userId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @return DAOResultFactory
	 */
	function getAllBySymbolic($symbolic, $monographFileId = null, $userId = null, $userGroupId = null) {
		return parent::getAllBySymbolic($symbolic, ASSOC_TYPE_SUBMISSION_FILE, $monographFileId, $userId, $userGroupId);
	}

	/**
	 * Retrieve all signoffs matching the specified input parameters
	 * @param $monographId int
	 * @param $symbolic string (optional)
	 * @param $userId int
	 * @param $userGroupId int
	 * @param $onlyCompleted boolean
	 * @return DAOResultFactory
	 */
	function getAllByMonograph($monographId, $symbolic = null, $userId = null, $userGroupId = null, $notCompletedOnly = false) {
		$sql = 'SELECT s.* FROM signoffs s, submission_files sf WHERE s.assoc_type = ? AND s.assoc_id = sf.file_id AND sf.submission_id = ?';
		$params = array(ASSOC_TYPE_SUBMISSION_FILE, (int) $monographId);

		if ($symbolic) {
			$sql .= ' AND s.symbolic = ?';
			$params[] = $symbolic;
		}
		if ($userId) {
			$sql .= ' AND user_id = ?';
			$params[] = (int) $userId;
		}

		if ($userGroupId) {
			$sql .= ' AND user_group_id = ?';
			$params[] = (int) $userGroupId;
		}

		if ($notCompletedOnly) {
			$sql .= ' AND date_completed IS NULL';
		}

		$result = $this->retrieve($sql, $params);
		return new DAOResultFactory($result, $this, '_fromRow', array('id'));
	}
}

?>
