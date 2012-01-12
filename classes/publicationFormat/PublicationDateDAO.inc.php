<?php

/**
 * @file classes/publicationFormat/PublicationDateDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateDAO
 * @ingroup publicationFormat
 * @see PublicationDate
 *
 * @brief Operations for retrieving and modifying PublicationDate objects.
 */

import('classes.publicationFormat.PublicationDate');

class PublicationDateDAO extends DAO {
	/**
	 * Constructor
	 */
	function PublicationDateDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a publication date by type id.
	 * @param $publicationDateId int
	 * @param $monographId optional int
	 * @return PublicationDate
	 */
	function &getById($publicationDateId, $monographId = null){
		$sqlParams = array((int) $publicationDateId);
		if ($monographId) {
			$sqlParams[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT p.*
				FROM publication_dates p
			JOIN published_monograph_publication_formats pmpf ON (p.assigned_publication_format_id = pmpf.assigned_publication_format_id)
			WHERE p.publication_date_id = ?
				' . ($monographId?' AND pmpf.monograph_id = ?':''),
			$sqlParams);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all publication dates for an assigned publication format
	 * @param $assignedPublicationFormatId int
	 * @return DAOResultFactory containing matching publication dates
	 */
	function &getByAssignedPublicationFormatId($assignedPublicationFormatId) {
		$result =& $this->retrieveRange(
			'SELECT * FROM publication_dates WHERE assigned_publication_format_id = ?', array((int) $assignedPublicationFormatId));

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return PublicationDate
	 */
	function newDataObject() {
		return new PublicationDate();
	}

	/**
	 * Internal function to return a PublicationDate object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return PublicationDate
	 */
	function &_fromRow(&$row, $callHooks = true) {
		$publicationDate = $this->newDataObject();
		$publicationDate->setId($row['publication_date_id']);
		$publicationDate->setRole($row['role']);
		$publicationDate->setDateFormat($row['date_format']);
		$publicationDate->setDate($row['date']);
		$publicationDate->setAssignedPublicationFormatId($row['assigned_publication_format_id']);

		if ($callHooks) HookRegistry::call('PublicationDateDAO::_fromRow', array(&$publicationDate, &$row));

		return $publicationDate;
	}

	/**
	 * Insert a new publication date.
	 * @param $publicationDate PublicationDate
	 */
	function insertObject(&$publicationDate) {
		$this->update(
			'INSERT INTO publication_dates
				(assigned_publication_format_id, role, date_format, date)
			VALUES
				(?, ?, ?, ?)',
			array(
				(int) $publicationDate->getAssignedPublicationFormatId(),
				$publicationDate->getRole(),
				$publicationDate->getDateFormat(),
				$publicationDate->getDate()
			)
		);

		$publicationDate->setId($this->getInsertPublicationDateId());
		return $publicationDate->getId();
	}

	/**
	 * Update an existing publication date.
	 * @param $publicationDate PublicationDate
	 */
	function updateObject(&$publicationDate) {
		$this->update(
			'UPDATE publication_dates
				SET role = ?, date_format =?, date = ?
			WHERE publication_date_id = ?',
			array(
				$publicationDate->getRole(),
				$publicationDate->getDateFormat(),
				$publicationDate->getDate(),
				(int) $publicationDate->getId()
			)
		);
	}

	/**
	 * Delete a publication date.
	 * @param $publicationDate PublicationDate
	 */
	function deleteObject($publicationDate) {
		return $this->deleteById($publicationDate->getId());
	}

	/**
	 * delete a publication date by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'DELETE FROM publication_dates WHERE publication_date_id = ?', array((int) $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted publication date.
	 * @return int
	 */
	function getInsertPublicationDateId() {
		return $this->getInsertId('publication_dates', 'publication_date_id');
	}
}

?>
