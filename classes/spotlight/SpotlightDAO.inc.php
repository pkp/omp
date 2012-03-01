<?php

/**
 * @file classes/spotlight/SpotlightDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightDAO
 * @ingroup spotlight
 * @see Spotlight
 *
 * @brief Operations for retrieving and modifying Spotlight objects.
 */

import('classes.spotlight.Spotlight');

class SpotlightDAO extends DAO {
	/**
	 * Constructor
	 */
	function SpotlightDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a spotlight by spotlight ID.
	 * @param $spotlightId int
	 * @return Spotlight
	 */
	function &getById($spotlightId) {
		$result =& $this->retrieve(
			'SELECT * FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve spotlight Assoc ID by spotlight ID.
	 * @param $spotlightId int
	 * @return int
	 */
	function getSpotlightAssocId($spotlightId) {
		$result =& $this->retrieve(
			'SELECT assoc_id FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		return isset($result->fields[0]) ? $result->fields[0] : 0;
	}

	/**
	 * Retrieve spotlight Assoc ID by spotlight ID.
	 * @param $spotlightId int
	 * @return int
	 */
	function getSpotlightAssocType($spotlightId) {
		$result =& $this->retrieve(
			'SELECT assoc_type FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		return isset($result->fields[0]) ? $result->fields[0] : 0;
	}

	/**
	 * Get the list of localized field names for this table
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'description');
	}

	/**
	 * Get a new data object.
	 * @return DataObject
	 */
	function newDataObject() {
		return new Spotlight();
	}

	/**
	 * Internal function to return an Spotlight object from a row.
	 * @param $row array
	 * @return Spotlight
	 */
	function &_fromRow(&$row) {
		$spotlight = $this->newDataObject();
		$spotlight->setId($row['spotlight_id']);
		$spotlight->setAssocType($row['assoc_type']);
		$spotlight->setAssocId($row['assoc_id']);
		$spotlight->setPressId($row['press_id']);
		$spotlight->setLocation($row['location']);

		$this->getDataObjectSettings('spotlight_settings', 'spotlight_id', $row['spotlight_id'], $spotlight);

		return $spotlight;
	}

	/**
	 * Update the settings for this object
	 * @param $spotlight object
	 */
	function updateLocaleFields(&$spotlight) {
		$this->updateDataObjectSettings('spotlight_settings', $spotlight, array(
			'spotlight_id' => $spotlight->getId()
		));
	}

	/**
	 * Insert a new Spotlight.
	 * @param $spotlight Spotlight
	 * @return int
	 */
	function insertObject(&$spotlight) {
		$this->update(
			'INSERT INTO spotlights
				(assoc_type, assoc_id, press_id, location)
				VALUES
				(?, ?, ?, ?)',
			array(
				(int) $spotlight->getAssocType(),
				(int) $spotlight->getAssocId(),
				(int) $spotlight->getPressId(),
				(int) $spotlight->getLocation()
			)
		);
		$spotlight->setId($this->getInsertSpotlightId());
		$this->updateLocaleFields($spotlight);
		return $spotlight->getId();
	}

	/**
	 * Update an existing spotlight.
	 * @param $spotlight Spotlight
	 * @return boolean
	 */
	function updateObject(&$spotlight) {
		$returner = $this->update(
			'UPDATE spotlights
				SET
					assoc_type = ?,
					assoc_id = ?,
					press_id = ?,
					location = ?
				WHERE spotlight_id = ?',
			array(
				(int) $spotlight->getAssocType(),
				(int) $spotlight->getAssocId(),
				(int) $spotlight->getPressId(),
				(int) $spotlight->getLocation(),
				(int) $spotlight->getId()
			)
		);
		$this->updateLocaleFields($spotlight);
		return $returner;
	}

	/**
	 * Delete a spotlight.
	 * @param $spotlight Spotlight
	 * @return boolean
	 */
	function deleteObject($spotlight) {
		return $this->deleteById($spotlight->getId());
	}

	/**
	 * Delete an spotlight by spotlight ID.
	 * @param $spotlightId int
	 * @return boolean
	 */
	function deleteById($spotlightId) {
		$this->update('DELETE FROM spotlight_settings WHERE spotlight_id = ?', (int) $spotlightId);
		return $this->update('DELETE FROM spotlights WHERE spotlight_id = ?', (int) $spotlightId);
	}

	/**
	 * Delete spotlights by spotlight type ID.
	 * @param $typeId int
	 * @return boolean
	 */
	function deleteByTypeId($typeId) {
		$spotlights =& $this->getByTypeId($typeId);
		while (($spotlight =& $spotlights->next())) {
			$this->deleteObject($spotlight);
			unset($spotlight);
		}
	}

	/**
	 * Delete spotlights by Assoc ID
	 * @param $assocType int
	 * @param $assocId int
	 */
	function deleteByAssoc($assocType, $assocId) {
		$spotlights =& $this->getByAssocId($assocType, $assocId);
		while (($spotlight =& $spotlights->next())) {
			$this->deleteById($spotlight->getId());
			unset($spotlight);
		}
		return true;
	}

	/**
	 * Retrieve an array of spotlights matching a location and press id.
	 * @param $location int
	 * @param $pressId int
	 * @return object DAOResultFactory containing matching Spotlights
	 */
	function &getByLocationAndPressId($location, $pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT *
			FROM spotlights
			WHERE location = ? AND press_id = ?
			ORDER BY spotlight_id DESC',
			array((int) $location, (int) $pressId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve an array of spotlights matching a particular type ID.
	 * @param $typeId int
	 * @return object DAOResultFactory containing matching Spotlights
	 */
	function &getByTypeId($typeId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM spotlights WHERE type_id = ? ORDER BY spotlight_id DESC',
			(int) $typeId,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve an array of numSpotlights spotlights matching a particular Assoc ID.
	 * @param $assocType int
	 * @return object DAOResultFactory containing matching Spotlights
	 */
	function &getNumSpotlightsByAssocId($assocType, $assocId, $numSpotlights, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT *
			FROM spotlights
			WHERE assoc_type = ?
				AND assoc_id = ?
			ORDER BY spotlight_id DESC LIMIT ?',
			array((int) $assocType, (int) $assocId, (int) $numSpotlights),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve most recent spotlight by Assoc ID.
	 * @param $assocType int
	 * @return Spotlight
	 */
	function &getMostRecentSpotlightByAssocId($assocType, $assocId) {
		$result =& $this->retrieve(
			'SELECT *
			FROM spotlights
			WHERE assoc_type = ?
				AND assoc_id = ?
			ORDER BY spotlight_id DESC LIMIT 1',
			array((int) $assocType, (int) $assocId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get the ID of the last inserted spotlight.
	 * @return int
	 */
	function getInsertSpotlightId() {
		return $this->getInsertId('spotlights', 'spotlight_id');
	}
}

?>
