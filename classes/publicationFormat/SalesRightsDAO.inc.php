<?php

/**
 * @file classes/publicationFormat/SalesRightsDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsDAO
 * @ingroup publicationFormat
 *
 * @see SalesRights
 *
 * @brief Operations for retrieving and modifying SalesRights objects.
 */

namespace APP\publicationFormat;

use PKP\plugins\HookRegistry;
use PKP\db\DAOResultFactory;

use APP\publicationFormat\SalesRights;

class SalesRightsDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a sales rights entry by type id.
     *
     * @param $salesRightsId int
     * @param $publicationId optional int
     *
     * @return SalesRights|null
     */
    public function getById($salesRightsId, $publicationId = null)
    {
        $params = [(int) $salesRightsId];
        if ($publicationId) {
            $params[] = (int) $publicationId;
        }

        $result = $this->retrieve(
            'SELECT	s.*
			FROM	sales_rights s
				JOIN publication_formats pf ON (s.publication_format_id = pf.publication_format_id)
			WHERE s.sales_rights_id = ?
				' . ($publicationId ? ' AND pf.publication_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all sales rights for a publication format
     *
     * @param $publicationFormatId int
     *
     * @return DAOResultFactory containing matching sales rights.
     */
    public function getByPublicationFormatId($publicationFormatId)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM sales_rights WHERE publication_format_id = ?',
                [(int) $publicationFormatId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve the specific Sales Rights instance for which ROW is set to true.  There should only be one per format.
     *
     * @param $publicationFormatId int
     *
     * @return SalesRights|null
     */
    public function getROWByPublicationFormatId($publicationFormatId)
    {
        $result = $this->retrieve(
            'SELECT * FROM sales_rights WHERE row_setting = ? AND publication_format_id = ?',
            [1, (int) $publicationFormatId]
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return SalesRights
     */
    public function newDataObject()
    {
        return new SalesRights();
    }

    /**
     * Internal function to return a SalesRights object from a row.
     *
     * @param $row array
     * @param $callHooks boolean
     *
     * @return SalesRights
     */
    public function _fromRow($row, $callHooks = true)
    {
        $salesRights = $this->newDataObject();
        $salesRights->setId($row['sales_rights_id']);
        $salesRights->setType($row['type']);
        $salesRights->setROWSetting($row['row_setting']);
        $salesRights->setCountriesIncluded(unserialize($row['countries_included']));
        $salesRights->setCountriesExcluded(unserialize($row['countries_excluded']));
        $salesRights->setRegionsIncluded(unserialize($row['regions_included']));
        $salesRights->setRegionsExcluded(unserialize($row['regions_excluded']));

        $salesRights->setPublicationFormatId($row['publication_format_id']);

        if ($callHooks) {
            HookRegistry::call('SalesRightsDAO::_fromRow', [&$salesRights, &$row]);
        }

        return $salesRights;
    }

    /**
     * Insert a new sales rights entry.
     *
     * @param $salesRights SalesRights
     */
    public function insertObject($salesRights)
    {
        $this->update(
            'INSERT INTO sales_rights
				(publication_format_id, type, row_setting, countries_included, countries_excluded, regions_included, regions_excluded)
			VALUES
				(?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $salesRights->getPublicationFormatId(),
                $salesRights->getType(),
                $salesRights->getROWSetting(),
                serialize($salesRights->getCountriesIncluded() ? $salesRights->getCountriesIncluded() : []),
                serialize($salesRights->getCountriesExcluded() ? $salesRights->getCountriesExcluded() : []),
                serialize($salesRights->getRegionsIncluded() ? $salesRights->getRegionsIncluded() : []),
                serialize($salesRights->getRegionsExcluded() ? $salesRights->getRegionsExcluded() : [])
            ]
        );

        $salesRights->setId($this->getInsertId());
        return $salesRights->getId();
    }

    /**
     * Update an existing sales rights entry.
     *
     * @param $salesRights SalesRights
     */
    public function updateObject($salesRights)
    {
        $this->update(
            'UPDATE sales_rights
				SET type = ?,
				row_setting = ?,
				countries_included = ?,
				countries_excluded = ?,
				regions_included = ?,
				regions_excluded = ?
			WHERE sales_rights_id = ?',
            [
                $salesRights->getType(),
                $salesRights->getROWSetting(),
                serialize($salesRights->getCountriesIncluded() ? $salesRights->getCountriesIncluded() : []),
                serialize($salesRights->getCountriesExcluded() ? $salesRights->getCountriesExcluded() : []),
                serialize($salesRights->getRegionsIncluded() ? $salesRights->getRegionsIncluded() : []),
                serialize($salesRights->getRegionsExcluded() ? $salesRights->getRegionsExcluded() : []),
                (int) $salesRights->getId()
            ]
        );
    }

    /**
     * Delete a sales rights entry by id.
     *
     * @param $salesRights SalesRights
     */
    public function deleteObject($salesRights)
    {
        return $this->deleteById($salesRights->getId());
    }

    /**
     * delete a sales rights entry by id.
     *
     * @param $entryId int
     */
    public function deleteById($entryId)
    {
        $this->update('DELETE FROM sales_rights WHERE sales_rights_id = ?', [(int) $entryId]);
    }

    /**
     * Get the ID of the last inserted sales rights entry.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->_getInsertId('sales_rights', 'sales_rights_id');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\publicationFormat\SalesRightsDAO', '\SalesRightsDAO');
}
