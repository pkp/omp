<?php

/**
 * @file classes/publicationFormat/PublicationDateDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDateDAO
 *
 * @ingroup publicationFormat
 *
 * @see PublicationDate
 *
 * @brief Operations for retrieving and modifying PublicationDate objects.
 */

namespace APP\publicationFormat;

use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;

class PublicationDateDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a publication date by type id.
     *
     * @param int $publicationDateId
     * @param int $publicationId optional
     *
     * @return PublicationDate|null
     */
    public function getById($publicationDateId, $publicationId = null)
    {
        $params = [(int) $publicationDateId];
        if ($publicationId) {
            $params[] = (int) $publicationId;
        }

        $result = $this->retrieve(
            'SELECT p.*
			FROM	publication_dates p
				JOIN publication_formats pf ON (p.publication_format_id = pf.publication_format_id)
			WHERE p.publication_date_id = ?
				' . ($publicationId ? ' AND pf.publication_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all publication dates for an assigned publication format
     *
     * @param int $representationId
     *
     * @return DAOResultFactory<PublicationDate> containing matching publication dates
     */
    public function getByPublicationFormatId($representationId)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM publication_dates WHERE publication_format_id = ?',
                [(int) $representationId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return PublicationDate
     */
    public function newDataObject()
    {
        return new PublicationDate();
    }

    /**
     * Internal function to return a PublicationDate object from a row.
     *
     * @param array $row
     * @param bool $callHooks
     *
     * @return PublicationDate
     *
     * @hook PublicationDateDAO::_fromRow [[&$publicationDate, &$row]]
     */
    public function _fromRow($row, $callHooks = true)
    {
        $publicationDate = $this->newDataObject();
        $publicationDate->setId($row['publication_date_id']);
        $publicationDate->setRole($row['role']);
        $publicationDate->setDateFormat($row['date_format']);
        $publicationDate->setDate($row['date']);
        $publicationDate->setPublicationFormatId($row['publication_format_id']);

        if ($callHooks) {
            Hook::call('PublicationDateDAO::_fromRow', [&$publicationDate, &$row]);
        }

        return $publicationDate;
    }

    /**
     * Insert a new publication date.
     *
     * @param PublicationDate $publicationDate
     */
    public function insertObject($publicationDate)
    {
        $this->update(
            'INSERT INTO publication_dates
				(publication_format_id, role, date_format, date)
			VALUES
				(?, ?, ?, ?)',
            [
                (int) $publicationDate->getPublicationFormatId(),
                $publicationDate->getRole(),
                $publicationDate->getDateFormat(),
                $publicationDate->getDate()
            ]
        );

        $publicationDate->setId($this->getInsertId());
        return $publicationDate->getId();
    }

    /**
     * Update an existing publication date.
     *
     * @param PublicationDate $publicationDate
     */
    public function updateObject($publicationDate)
    {
        $this->update(
            'UPDATE publication_dates
				SET role = ?, date_format =?, date = ?
			WHERE publication_date_id = ?',
            [
                $publicationDate->getRole(),
                $publicationDate->getDateFormat(),
                $publicationDate->getDate(),
                (int) $publicationDate->getId()
            ]
        );
    }

    /**
     * Delete a publication date.
     */
    public function deleteObject(PublicationDate $publicationDate): int
    {
        return $this->deleteById($publicationDate->getId());
    }

    /**
     * Delete a publication date by id.
     */
    public function deleteById(int $entryId): int
    {
        return DB::table('publication_dates')
            ->where('publication_date_id', '=', $entryId)
            ->delete();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\publicationFormat\PublicationDateDAO', '\PublicationDateDAO');
}
