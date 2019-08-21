<?php

/**
 * @file classes/publicationFormat/PublicationDateDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a publication date by type id.
	 * @param $publicationDateId int
	 * @param $publicationId optional int
	 * @return PublicationDate
	 */
	function getById($publicationDateId, $publicationId = null){
		$sqlParams = array((int) $publicationDateId);
		if ($publicationId) {
			$sqlParams[] = (int) $publicationId;
		}

		$result = $this->retrieve(
			'SELECT p.*
			FROM	publication_dates p
				JOIN publication_formats pf ON (p.publication_format_id = pf.publication_format_id)
			WHERE p.publication_date_id = ?
				' . ($publicationId?' AND pf.publication_id = ?':''),
			$sqlParams
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all publication dates for an assigned publication format
	 * @param $representationId int
	 * @return DAOResultFactory containing matching publication dates
	 */
	function getByPublicationFormatId($representationId) {
		$result = $this->retrieveRange(
			'SELECT * FROM publication_dates WHERE publication_format_id = ?',
			(int) $representationId
		);

		return new DAOResultFactory($result, $this, '_fromRow');
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
	function _fromRow($row, $callHooks = true) {
		$publicationDate = $this->newDataObject();
		$publicationDate->setId($row['publication_date_id']);
		$publicationDate->setRole($row['role']);
		$publicationDate->setDateFormat($row['date_format']);
		$publicationDate->setDate($row['date']);
		$publicationDate->setPublicationFormatId($row['publication_format_id']);

		if ($callHooks) HookRegistry::call('PublicationDateDAO::_fromRow', array(&$publicationDate, &$row));

		return $publicationDate;
	}

	/**
	 * Insert a new publication date.
	 * @param $publicationDate PublicationDate
	 */
	function insertObject($publicationDate) {
		$this->update(
			'INSERT INTO publication_dates
				(publication_format_id, role, date_format, date)
			VALUES
				(?, ?, ?, ?)',
			array(
				(int) $publicationDate->getPublicationFormatId(),
				$publicationDate->getRole(),
				$publicationDate->getDateFormat(),
				$publicationDate->getDate()
			)
		);

		$publicationDate->setId($this->getInsertId());
		return $publicationDate->getId();
	}

	/**
	 * Update an existing publication date.
	 * @param $publicationDate PublicationDate
	 */
	function updateObject($publicationDate) {
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
	function getInsertId() {
		return $this->_getInsertId('publication_dates', 'publication_date_id');
	}
}


