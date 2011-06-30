<?php

/**
 * @file classes/monograph/MonographFileSignoffDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileSignoffDAO
 * @ingroup monograph
 * @see SignoffDAO
 *
 * @brief Extension of SignoffDAO to work with signoffs relating to monograph
 * files.
 */

import('lib.pkp.classes.signoff.SignoffDAO');

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
	function &getById($signoffId) {
		$returner =& parent::getById($signoffId, ASSOC_TYPE_MONOGRAPH_FILE);
		return $returner;
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
	function &build($symbolic, $monographFileId, $userId = null,
			$userGroupId = null, $fileId = null, $fileRevision = null) {
		$returner =& parent::build(
			$symbolic,
			ASSOC_TYPE_MONOGRAPH_FILE, $monographFileId,
			$userId, $userGroupId,
			$fileId, $fileRevision
		);
		return $returner;
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
		return parent::signoffExists($symbolic, ASSOC_TYPE_MONOGRAPH_FILE, $userId, $userGroupId);
	}

	/**
	 * @see SignoffDAO::newDataObject
	 */
	function newDataObject() {
		$signoff = parent::newDataObject();
		$signoff->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
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
		$returner = parent::getBySymbolic(
			$symbolic,
			ASSOC_TYPE_MONOGRAPH_FILE, $monographFileId,
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
		return parent::getAllBySymbolic($symbolic, ASSOC_TYPE_MONOGRAPH_FILE, $monographFileId, $userId, $userGroupId);
	}

	/**
	 * Retrieve all signoffs matching the specified input parameters
	 * @param $symbolic string
	 * @param $assocType int
	 * @param $assocId int
	 * @param $userId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @return DAOResultFactory
	 */
	function getAllByMonograph($symbolic, $monographId, $userId = null, $userGroupId = null) {
		$sql = 'SELECT s.* FROM signoffs s, monograph_files mf WHERE s.symbolic = ? AND s.assoc_type = ? AND s.assoc_id = mf.file_id AND mf.monograph_id = ?';
		$params = array($symbolic, ASSOC_TYPE_MONOGRAPH_FILE, (int) $monographId);

		if ($userId) {
			$sql .= ' AND user_id = ?';
			$params[] = (int) $userId;
		}

		if ($userGroupId) {
			$sql .= ' AND user_group_id = ?';
			$params[] = (int) $userGroupId;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
		return $returner;
	}
}

?>
