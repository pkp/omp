<?php

/**
 * @file classes/spotlight/SpotlightDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SpotlightDAO
 * @ingroup spotlight
 *
 * @see Spotlight
 *
 * @brief Operations for retrieving and modifying Spotlight objects.
 */

namespace APP\spotlight;

use PKP\db\DAOResultFactory;

class SpotlightDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a spotlight by spotlight ID.
     *
     * @param int $spotlightId
     *
     * @return Spotlight|null
     */
    public function getById($spotlightId)
    {
        $result = $this->retrieve(
            'SELECT * FROM spotlights WHERE spotlight_id = ?',
            [(int) $spotlightId]
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve spotlight Assoc ID by spotlight ID.
     *
     * @param int $spotlightId
     *
     * @return int
     */
    public function getSpotlightAssocId($spotlightId)
    {
        $result = $this->retrieve(
            'SELECT assoc_id FROM spotlights WHERE spotlight_id = ?',
            [(int) $spotlightId]
        );
        $row = $result->current();
        return $row ? $row->assoc_id : 0;
    }

    /**
     * Retrieve spotlight Assoc ID by spotlight ID.
     *
     * @param int $spotlightId
     *
     * @return int
     */
    public function getSpotlightAssocType($spotlightId)
    {
        $result = $this->retrieve(
            'SELECT assoc_type FROM spotlights WHERE spotlight_id = ?',
            [(int) $spotlightId]
        );
        $row = $result->current();
        return $row ? $row->assoc_type : 0;
    }

    /**
     * Get the list of localized field names for this table
     *
     * @return array
     */
    public function getLocaleFieldNames()
    {
        return ['title', 'description'];
    }

    /**
     * Get a new data object.
     *
     * @return DataObject
     */
    public function newDataObject()
    {
        return new Spotlight();
    }

    /**
     * Internal function to return an Spotlight object from a row.
     *
     * @param array $row
     *
     * @return Spotlight
     */
    public function _fromRow($row)
    {
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
     *
     * @param object $spotlight
     */
    public function updateLocaleFields($spotlight)
    {
        $this->updateDataObjectSettings(
            'spotlight_settings',
            $spotlight,
            ['spotlight_id' => $spotlight->getId()]
        );
    }

    /**
     * Insert a new Spotlight.
     *
     * @param Spotlight $spotlight
     *
     * @return int
     */
    public function insertObject($spotlight)
    {
        $this->update(
            'INSERT INTO spotlights
				(assoc_type, assoc_id, press_id)
				VALUES
				(?, ?, ?)',
            [
                (int) $spotlight->getAssocType(),
                (int) $spotlight->getAssocId(),
                (int) $spotlight->getPressId(),
            ]
        );
        $spotlight->setId($this->getInsertId());
        $this->updateLocaleFields($spotlight);
        return $spotlight->getId();
    }

    /**
     * Update an existing spotlight.
     *
     * @param Spotlight $spotlight
     *
     * @return bool
     */
    public function updateObject($spotlight)
    {
        $returner = $this->update(
            'UPDATE spotlights
				SET
					assoc_type = ?,
					assoc_id = ?,
					press_id = ?
				WHERE spotlight_id = ?',
            [
                (int) $spotlight->getAssocType(),
                (int) $spotlight->getAssocId(),
                (int) $spotlight->getPressId(),
                (int) $spotlight->getId()
            ]
        );
        $this->updateLocaleFields($spotlight);
        return $returner;
    }

    /**
     * Delete a spotlight.
     *
     * @param Spotlight $spotlight
     *
     * @return bool
     */
    public function deleteObject($spotlight)
    {
        return $this->deleteById($spotlight->getId());
    }

    /**
     * Delete an spotlight by spotlight ID.
     *
     * @param int $spotlightId
     */
    public function deleteById($spotlightId)
    {
        $this->update('DELETE FROM spotlight_settings WHERE spotlight_id = ?', [(int) $spotlightId]);
        $this->update('DELETE FROM spotlights WHERE spotlight_id = ?', [(int) $spotlightId]);
    }

    /**
     * Delete spotlights by spotlight type ID.
     *
     * @param int $typeId
     *
     * @return bool
     */
    public function deleteByTypeId($typeId)
    {
        $spotlights = $this->getByTypeId($typeId);
        while (($spotlight = $spotlights->next())) {
            $this->deleteObject($spotlight);
        }
    }

    /**
     * Delete spotlights by Assoc ID
     *
     * @param int $assocType
     * @param int $assocId
     */
    public function deleteByAssoc($assocType, $assocId)
    {
        $spotlights = $this->getByAssocId($assocType, $assocId);
        while ($spotlight = $spotlights->next()) {
            $this->deleteById($spotlight->getId());
        }
    }

    /**
     * Retrieve an array of spotlights matching a press id.
     *
     * @param int $pressId
     * @param null|mixed $rangeInfo
     *
     * @return array Array containing matching Spotlights
     */
    public function getByPressId($pressId, $rangeInfo = null)
    {
        $result = $this->retrieveRange(
            'SELECT *
			FROM spotlights
			WHERE press_id = ?
			ORDER BY spotlight_id DESC',
            [(int) $pressId],
            $rangeInfo
        );
        $returner = [];
        foreach ($result as $row) {
            $spotlight = $this->_fromRow((array) $row);
            if ($spotlight->getSpotlightItem()) {
                $returner[$spotlight->getId()] = $spotlight;
            }
        }
        return $returner;
    }

    /**
     * Retrieve a random spotlight matching a press id.
     *
     * @param int $pressId
     * @param int $quantity (optional) If more than one is needed,
     * specify here.
     *
     * @return array or null
     */
    public function getRandomByPressId($pressId, $quantity = 1)
    {
        $spotlights = array_values($this->getByPressId($pressId));
        $returner = [];
        if (count($spotlights) > 0) {
            if (count($spotlights) <= $quantity) {
                // Return the ones that we have.
                $returner = $spotlights;
            } else {
                // Get the random spotlights.
                for ($quantity; $quantity > 0; $quantity--) {
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
     *
     * @param int $assocType
     * @param int $assocId
     * @param null|mixed $rangeInfo
     *
     * @return object DAOResultFactory containing matching Spotlights
     */
    public function getByAssoc($assocType, $assocId, $rangeInfo = null)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT *
				FROM spotlights
				WHERE assoc_type = ? AND assoc_id = ?
				ORDER BY spotlight_id DESC',
                [(int) $assocType, (int) $assocId],
                $rangeInfo
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve an array of numSpotlights spotlights matching a particular Assoc ID.
     *
     * @param int $assocType
     * @param null|mixed $rangeInfo
     *
     * @return object DAOResultFactory containing matching Spotlights
     */
    public function getNumSpotlightsByAssoc($assocType, $assocId, $rangeInfo = null)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT *
				FROM spotlights
				WHERE assoc_type = ?
					AND assoc_id = ?
				ORDER BY spotlight_id DESC',
                [(int) $assocType, (int) $assocId],
                $rangeInfo
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve most recent spotlight by Assoc ID.
     *
     * @param int $assocType
     *
     * @return Spotlight|null
     */
    public function getMostRecentSpotlightByAssoc($assocType, $assocId)
    {
        $result = $this->retrieve(
            'SELECT *
			FROM spotlights
			WHERE assoc_type = ?
				AND assoc_id = ?
			ORDER BY spotlight_id DESC LIMIT 1',
            [(int) $assocType, (int) $assocId]
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\spotlight\SpotlightDAO', '\SpotlightDAO');
}
