<?php

/**
 * @file classes/publicationFormat/SalesRightsDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsDAO
 *
 * @ingroup publicationFormat
 *
 * @see SalesRights
 *
 * @brief Operations for retrieving and modifying SalesRights objects.
 */

namespace APP\publicationFormat;

use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;

class SalesRightsDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a sales rights entry by type id.
     *
     * @param int $salesRightsId
     * @param int $publicationId optional int
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
     * @param int $publicationFormatId
     *
     * @return DAOResultFactory<SalesRights> containing matching sales rights.
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
     * @param int $publicationFormatId
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
     * @param array $row
     * @param bool $callHooks
     *
     * @return SalesRights
     *
     * @hook SalesRightsDAO::_fromRow [[&$salesRights, &$row]]
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
            Hook::call('SalesRightsDAO::_fromRow', [&$salesRights, &$row]);
        }

        return $salesRights;
    }

    /**
     * Insert a new sales rights entry.
     *
     * @param SalesRights $salesRights
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
     * @param SalesRights $salesRights
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
     */
    public function deleteObject(SalesRights $salesRights): int
    {
        return $this->deleteById($salesRights->getId());
    }

    /**
     * Delete a sales rights entry by id.
     */
    public function deleteById(int $entryId): int
    {
        return DB::table('sales_rights')
            ->where('sales_rights_id', '=', $entryId)
            ->delete();
    }
}
