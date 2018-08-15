<?php

/**
 * @file classes/spotlight/SpotlightDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
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
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a spotlight by spotlight ID.
	 * @param $spotlightId int
	 * @return Spotlight
	 */
	function getById($spotlightId) {
		$result = $this->retrieve(
			'SELECT * FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
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
		$result = $this->retrieve(
			'SELECT assoc_id FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		$returner = isset($result->fields[0]) ? $result->fields[0] : 0;
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve spotlight Assoc ID by spotlight ID.
	 * @param $spotlightId int
	 * @return int
	 */
	function getSpotlightAssocType($spotlightId) {
		$result = $this->retrieve(
			'SELECT assoc_type FROM spotlights WHERE spotlight_id = ?',
			(int) $spotlightId
		);

		$returner = isset($result->fields[0]) ? $result->fields[0] : 0;
		$result->Close();
		return $returner;
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
	function _fromRow($row) {
		$spotlight = $this->newDataObject();
		$spotlight->setId($row['spotlight_id']);
		$spotlight->setAssocType($row['assoc_type']);
		$spotlight->setAssocId($row['assoc_id']);
		$spotlight->setPressId($row['press_id']);

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
	function insertObject($spotlight) {
		$this->update(
			'INSERT INTO spotlights
				(assoc_type, assoc_id, press_id)
				VALUES
				(?, ?, ?)',
			array(
				(int) $spotlight->getAssocType(),
				(int) $spotlight->getAssocId(),
				(int) $spotlight->getPressId(),
			)
		);
		$spotlight->setId($this->getInsertId());
		$this->updateLocaleFields($spotlight);
		return $spotlight->getId();
	}

	/**
	 * Update an existing spotlight.
	 * @param $spotlight Spotlight
	 * @return boolean
	 */
	function updateObject($spotlight) {
		$returner = $this->update(
			'UPDATE spotlights
				SET
					assoc_type = ?,
					assoc_id = ?,
					press_id = ?
				WHERE spotlight_id = ?',
			array(
				(int) $spotlight->getAssocType(),
				(int) $spotlight->getAssocId(),
				(int) $spotlight->getPressId(),
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
		$spotlights = $this->getByTypeId($typeId);
		while (($spotlight = $spotlights->next())) {
			$this->deleteObject($spotlight);
		}
	}

	/**
	 * Delete spotlights by Assoc ID
	 * @param $assocType int
	 * @param $assocId int
	 */
	function deleteByAssoc($assocType, $assocId) {
		$spotlights = $this->getByAssocId($assocType, $assocId);
		while ($spotlight = $spotlights->next()) {
			$this->deleteById($spotlight->getId());
		}
		return true;
	}

	/**
	 * Retrieve an array of spotlights matching a press id.
	 * @param $pressId int
	 * @return array Array containing matching Spotlights
	 */
	function getByPressId($pressId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT *
			FROM spotlights
			WHERE press_id = ?
			ORDER BY spotlight_id DESC',
			array((int) $pressId),
			$rangeInfo
		);

		$spotlightFactory = new DAOResultFactory($result, $this, '_fromRow');
		$returner = array();

		// Avoid spotlights without items.
		while ($spotlight = $spotlightFactory->next()) {
			$spotlightItem = $spotlight->getSpotlightItem();
			if ($spotlightItem) {
				$returner[$spotlight->getId()] = $spotlight;
			}
		}

		return $returner;
	}

	/**
	 * Retrieve a random spotlight matching a press id.
	 * @param $pressId int
	 * @param $quantity int (optional) If more than one is needed,
	 * specify here.
	 * @return array or null
	 */
	function getRandomByPressId($pressId, $quantity = 1) {
		$spotlights = array_values($this->getByPressId($pressId));
		$returner = array();
		if (count($spotlights) > 0) {
			if (count($spotlights) <= $quantity) {
				// Return the ones that we have.
				$returner = $spotlights;
			} else {
				// Get the random spotlights.
				for($quantity; $quantity > 0; $quantity--) {
					$randNumber = rand(0, count($spotlights) - 1);
					$returner[] = $spotlights[$randNumber];
					unset($spotlights[$randNumber]);
					// Reset spotlights array index.
					$spotlights = array_values($spotlights);
				}
			}
		}

		if (count($returner) == 0) {
			$returner = null;
		}

		return $returner;
	}

	/**
	 * Retrieve an array of spotlights matching a particular assoc ID.
	 * @param $assocType int
	 * @param $assocId int
	 * @return object DAOResultFactory containing matching Spotlights
	 */
	function getByAssoc($assocType, $assocId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT *
			FROM spotlights
			WHERE assoc_type = ? AND assoc_id = ?
			ORDER BY spotlight_id DESC',
			array((int) $assocType, (int) $assocId),
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve an array of numSpotlights spotlights matching a particular Assoc ID.
	 * @param $assocType int
	 * @return object DAOResultFactory containing matching Spotlights
	 */
	function getNumSpotlightsByAssoc($assocType, $assocId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT *
			FROM spotlights
			WHERE assoc_type = ?
				AND assoc_id = ?
			ORDER BY spotlight_id DESC',
			array((int) $assocType, (int) $assocId),
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve most recent spotlight by Assoc ID.
	 * @param $assocType int
	 * @return Spotlight
	 */
	function getMostRecentSpotlightByAssoc($assocType, $assocId) {
		$result = $this->retrieve(
			'SELECT *
			FROM spotlights
			WHERE assoc_type = ?
				AND assoc_id = ?
			ORDER BY spotlight_id DESC LIMIT 1',
			array((int) $assocType, (int) $assocId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get the ID of the last inserted spotlight.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('spotlights', 'spotlight_id');
	}
}


