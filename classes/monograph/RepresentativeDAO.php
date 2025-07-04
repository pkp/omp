<?php

/**
 * @file classes/monograph/RepresentativeDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RepresentativeDAO
 *
 * @ingroup monograph
 *
 * @see Representative
 *
 * @brief Operations for retrieving and modifying Representative (suppliers and agents) objects.
 */

namespace APP\monograph;

use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;

class RepresentativeDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a representative entry by id.
     *
     * @param int $representativeId
     * @param int $monographId optional
     *
     * @return Representative|null
     */
    public function getById($representativeId, $monographId = null)
    {
        $params = [(int) $representativeId];
        if ($monographId) {
            $params[] = (int) $monographId;
        }

        $result = $this->retrieve(
            'SELECT r.*
				FROM representatives r
			JOIN submissions s ON (r.submission_id = s.submission_id)
			WHERE r.representative_id = ?
				' . ($monographId ? ' AND s.submission_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all supplier representatives for a monograph.
     *
     * @param int $monographId
     *
     * @return DAOResultFactory<Representative> containing matching representatives.
     */
    public function getSuppliersByMonographId($monographId)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM representatives WHERE submission_id = ? AND is_supplier = ?',
                [(int) $monographId, 1]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve all agent representatives for a monograph.
     *
     * @param int $monographId
     *
     * @return DAOResultFactory<Representative> containing matching representatives.
     */
    public function getAgentsByMonographId($monographId)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM representatives WHERE submission_id = ? AND is_supplier = ?',
                [(int) $monographId, 0]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return Representative
     */
    public function newDataObject()
    {
        return new Representative();
    }

    /**
     * Internal function to return a Representative object from a row.
     *
     * @param array $row
     * @param bool $callHooks
     *
     * @return Representative
     *
     * @hook RepresentativeDAO::_fromRow [[&$representative, &$row]]
     */
    public function _fromRow($row, $callHooks = true)
    {
        $representative = $this->newDataObject();
        $representative->setId($row['representative_id']);
        $representative->setRole($row['role']);
        $representative->setRepresentativeIdType($row['representative_id_type']);
        $representative->setRepresentativeIdValue($row['representative_id_value']);
        $representative->setName($row['name']);
        $representative->setPhone($row['phone']);
        $representative->setEmail($row['email']);
        $representative->setUrl($row['url']);
        $representative->setIsSupplier($row['is_supplier']);
        $representative->setMonographId($row['submission_id']);

        if ($callHooks) {
            Hook::call('RepresentativeDAO::_fromRow', [&$representative, &$row]);
        }

        return $representative;
    }

    /**
     * Insert a new representative entry.
     *
     * @param Representative $representative
     */
    public function insertObject($representative)
    {
        $this->update(
            'INSERT INTO representatives
				(submission_id, role, representative_id_type, representative_id_value, name, phone, email, url, is_supplier)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $representative->getMonographId(),
                $representative->getRole(),
                $representative->getRepresentativeIdType(),
                $representative->getRepresentativeIdValue(),
                $representative->getName(),
                $representative->getPhone(),
                $representative->getEmail(),
                $representative->getUrl(),
                (int) $representative->getIsSupplier()
            ]
        );

        $representative->setId($this->getInsertId());
        return $representative->getId();
    }

    /**
     * Update an existing representative entry.
     *
     * @param Representative $representative
     */
    public function updateObject($representative)
    {
        $this->update(
            'UPDATE representatives
				SET role = ?,
				representative_id_type = ?,
				representative_id_value = ?,
				name = ?,
				phone = ?,
				email = ?,
				url = ?,
				is_supplier = ?
			WHERE representative_id = ?',
            [
                $representative->getRole(),
                $representative->getRepresentativeIdType(),
                $representative->getRepresentativeIdValue(),
                $representative->getName(),
                $representative->getPhone(),
                $representative->getEmail(),
                $representative->getUrl(),
                (int) $representative->getIsSupplier(),
                (int) $representative->getId()
            ]
        );
    }

    /**
     * Delete a representative entry by object.
     *
     * @param Representative $representative
     */
    public function deleteObject($representative)
    {
        return $this->deleteById($representative->getId());
    }

    /**
     * Delete a representative entry by id.
     */
    public function deleteById(int $entryId)
    {
        return DB::table('representatives')
            ->where('representative_id', '=', $entryId)
            ->delete();
    }
}
