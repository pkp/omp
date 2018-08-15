<?php

/**
 * @file classes/publicationFormat/SalesRightsDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsDAO
 * @ingroup publicationFormat
 * @see SalesRights
 *
 * @brief Operations for retrieving and modifying SalesRights objects.
 */

import('classes.publicationFormat.SalesRights');

class SalesRightsDAO extends DAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a sales rights entry by type id.
	 * @param $salesRightsId int
	 * @param $monographId optional int
	 * @return SalesRights
	 */
	function getById($salesRightsId, $monographId = null){
		$sqlParams = array((int) $salesRightsId);
		if ($monographId) {
			$sqlParams[] = (int) $monographId;
		}

		$result = $this->retrieve(
			'SELECT	s.*
			FROM	sales_rights s
				JOIN publication_formats pf ON (s.publication_format_id = pf.publication_format_id)
			WHERE s.sales_rights_id = ?
				' . ($monographId?' AND pf.submission_id = ?':''),
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
	 * Retrieve all sales rights for a publication format
	 * @param $publicationFormatId int
	 * @return DAOResultFactory containing matching sales rights.
	 */
	function getByPublicationFormatId($publicationFormatId) {
		$result = $this->retrieveRange(
			'SELECT * FROM sales_rights WHERE publication_format_id = ?', (int) $publicationFormatId);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve the specific Sales Rights instance for which ROW is set to true.  There should only be one per format.
	 * @param $publicationFormatId int
	 * @return SalesRights
	 */
	function getROWByPublicationFormatId($publicationFormatId) {
		$result = $this->retrieve(
			'SELECT * FROM sales_rights WHERE row_setting = ? AND publication_format_id = ?',
			array(1, (int) $publicationFormatId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SalesRights
	 */
	function newDataObject() {
		return new SalesRights();
	}

	/**
	 * Internal function to return a SalesRights object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return SalesRights
	 */
	function _fromRow($row, $callHooks = true) {
		$salesRights = $this->newDataObject();
		$salesRights->setId($row['sales_rights_id']);
		$salesRights->setType($row['type']);
		$salesRights->setROWSetting($row['row_setting']);
		$salesRights->setCountriesIncluded(unserialize($row['countries_included']));
		$salesRights->setCountriesExcluded(unserialize($row['countries_excluded']));
		$salesRights->setRegionsIncluded(unserialize($row['regions_included']));
		$salesRights->setRegionsExcluded(unserialize($row['regions_excluded']));

		$salesRights->setPublicationFormatId($row['publication_format_id']);

		if ($callHooks) HookRegistry::call('SalesRightsDAO::_fromRow', array(&$salesRights, &$row));

		return $salesRights;
	}

	/**
	 * Insert a new sales rights entry.
	 * @param $salesRights SalesRights
	 */
	function insertObject($salesRights) {
		$this->update(
			'INSERT INTO sales_rights
				(publication_format_id, type, row_setting, countries_included, countries_excluded, regions_included, regions_excluded)
			VALUES
				(?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $salesRights->getPublicationFormatId(),
				$salesRights->getType(),
				$salesRights->getROWSetting(),
				serialize($salesRights->getCountriesIncluded() ? $salesRights->getCountriesIncluded() : array()),
				serialize($salesRights->getCountriesExcluded() ? $salesRights->getCountriesExcluded() : array()),
				serialize($salesRights->getRegionsIncluded() ? $salesRights->getRegionsIncluded() : array()),
				serialize($salesRights->getRegionsExcluded() ? $salesRights->getRegionsExcluded() : array())
			)
		);

		$salesRights->setId($this->getInsertId());
		return $salesRights->getId();
	}

	/**
	 * Update an existing sales rights entry.
	 * @param $salesRights SalesRights
	 */
	function updateObject($salesRights) {
		$this->update(
			'UPDATE sales_rights
				SET type = ?,
				row_setting = ?,
				countries_included = ?,
				countries_excluded = ?,
				regions_included = ?,
				regions_excluded = ?
			WHERE sales_rights_id = ?',
			array(
				$salesRights->getType(),
				$salesRights->getROWSetting(),
				serialize($salesRights->getCountriesIncluded() ? $salesRights->getCountriesIncluded() : array()),
				serialize($salesRights->getCountriesExcluded() ? $salesRights->getCountriesExcluded() : array()),
				serialize($salesRights->getRegionsIncluded() ? $salesRights->getRegionsIncluded() : array()),
				serialize($salesRights->getRegionsExcluded() ? $salesRights->getRegionsExcluded() : array()),
				(int) $salesRights->getId()
			)
		);
	}

	/**
	 * Delete a sales rights entry by id.
	 * @param $salesRights SalesRights
	 */
	function deleteObject($salesRights) {
		return $this->deleteById($salesRights->getId());
	}

	/**
	 * delete a sales rights entry by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'DELETE FROM sales_rights WHERE sales_rights_id = ?', array((int) $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted sales rights entry.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('sales_rights', 'sales_rights_id');
	}
}


