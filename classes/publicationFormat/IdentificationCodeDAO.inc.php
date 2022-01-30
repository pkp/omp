<?php

/**
 * @file classes/publicationFormat/IdentificationCodeDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCodeDAO
 * @ingroup publicationFormat
 *
 * @see IdentificationCode
 *
 * @brief Operations for retrieving and modifying IdentificationCode objects.
 */

namespace APP\publicationFormat;

use APP\publicationFormat\IdentificationCode;

use PKP\plugins\HookRegistry;
use PKP\db\DAOResultFactory;

class IdentificationCodeDAO extends \PKP\db\DAO
{
    /**
     * Retrieve an identification code by type id.
     *
     * @param int $identificationCodeId
     * @param int $publicationId optional
     *
     * @return IdentificationCode|null
     */
    public function getById($identificationCodeId, $publicationId = null)
    {
        $params = [(int) $identificationCodeId];
        if ($publicationId) {
            $params[] = (int) $publicationId;
        }

        $result = $this->retrieve(
            'SELECT	i.*
			FROM	identification_codes i
				JOIN publication_formats pf ON (i.publication_format_id = pf.publication_format_id)
			WHERE i.identification_code_id = ?
				' . ($publicationId ? ' AND pf.publication_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all identification codes for a publication format
     *
     * @param int $publicationFormatId
     *
     * @return DAOResultFactory containing matching identification codes
     */
    public function getByPublicationFormatId($publicationFormatId)
    {
        return new DAOResultFactory(
            $result = $this->retrieveRange(
                'SELECT * FROM identification_codes WHERE publication_format_id = ?',
                [(int) $publicationFormatId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return IdentificationCode
     */
    public function newDataObject()
    {
        return new IdentificationCode();
    }

    /**
     * Internal function to return a IdentificationCode object from a row.
     *
     * @param array $row
     * @param bool $callHooks
     *
     * @return IdentificationCode
     */
    public function _fromRow($row, $callHooks = true)
    {
        $identificationCode = $this->newDataObject();
        $identificationCode->setId($row['identification_code_id']);
        $identificationCode->setCode($row['code']);
        $identificationCode->setValue($row['value']);
        $identificationCode->setPublicationFormatId($row['publication_format_id']);

        if ($callHooks) {
            HookRegistry::call('IdentificationCodeDAO::_fromRow', [&$identificationCode, &$row]);
        }

        return $identificationCode;
    }

    /**
     * Insert a new identification code.
     *
     * @param IdentificationCode $identificationCode
     */
    public function insertObject($identificationCode)
    {
        $this->update(
            'INSERT INTO identification_codes
				(publication_format_id, code, value)
			VALUES
				(?, ?, ?)',
            [
                (int) $identificationCode->getPublicationFormatId(),
                $identificationCode->getCode(),
                $identificationCode->getValue()
            ]
        );

        $identificationCode->setId($this->getInsertId());
        return $identificationCode->getId();
    }

    /**
     * Update an existing identification code.
     *
     * @param IdentificationCode $identificationCode
     */
    public function updateObject($identificationCode)
    {
        $this->update(
            'UPDATE identification_codes
				SET code = ?, value = ?
			WHERE identification_code_id = ?',
            [
                $identificationCode->getCode(),
                $identificationCode->getValue(),
                (int) $identificationCode->getId()
            ]
        );
    }

    /**
     * Delete an identification code by id.
     *
     * @param IdentificationCode $identificationCode
     */
    public function deleteObject($identificationCode)
    {
        return $this->deleteById($identificationCode->getId());
    }

    /**
     * delete a identification code by id.
     *
     * @param int $entryId
     */
    public function deleteById($entryId)
    {
        return $this->update(
            'DELETE FROM identification_codes WHERE identification_code_id = ?',
            [(int) $entryId]
        );
    }

    /**
     * Get the ID of the last inserted identification code.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->_getInsertId('identification_codes', 'identification_code_id');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\publicationFormat\IdentificationCodeDAO', '\IdentificationCodeDAO');
}
