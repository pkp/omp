<?php

/**
 * @file classes/publicationFormat/MarketDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MarketDAO
 *
 * @ingroup publicationFormat
 *
 * @see Market
 *
 * @brief Operations for retrieving and modifying Market objects.
 */

namespace APP\publicationFormat;

use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;

class MarketDAO extends \PKP\db\DAO
{
    /**
     * Retrieve a market entry by type id.
     *
     * @param int $marketId
     * @param int $publicationId
     *
     * @return Market|null
     */
    public function getById($marketId, $publicationId = null)
    {
        $params = [(int) $marketId];
        if ($publicationId) {
            $params[] = (int) $publicationId;
        }

        $result = $this->retrieve(
            'SELECT	m.*
			FROM	markets m
				JOIN publication_formats pf ON (m.publication_format_id = pf.publication_format_id)
			WHERE	m.market_id = ?
				' . ($publicationId ? ' AND pf.publication_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Retrieve all market for a publication format
     *
     * @param int $publicationFormatId
     *
     * @return DAOResultFactory<Market> containing matching market.
     */
    public function getByPublicationFormatId($publicationFormatId)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM markets WHERE publication_format_id = ?',
                [(int) $publicationFormatId]
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return Market
     */
    public function newDataObject()
    {
        return new Market();
    }

    /**
     * Internal function to return a Market object from a row.
     *
     * @param array $row
     * @param bool $callHooks
     *
     * @return Market
     *
     * @hook MarketDAO::_fromRow [[&$market, &$row]]
     */
    public function _fromRow($row, $callHooks = true)
    {
        $market = $this->newDataObject();
        $market->setId($row['market_id']);
        $market->setCountriesIncluded(unserialize($row['countries_included']));
        $market->setCountriesExcluded(unserialize($row['countries_excluded']));
        $market->setRegionsIncluded(unserialize($row['regions_included']));
        $market->setRegionsExcluded(unserialize($row['regions_excluded']));
        $market->setDateRole($row['market_date_role']);
        $market->setDateFormat($row['market_date_format']);
        $market->setDate($row['market_date']);
        $market->setDiscount($row['discount']);
        $market->setPrice($row['price']);
        $market->setPriceTypeCode($row['price_type_code']);
        $market->setCurrencyCode($row['currency_code']);
        $market->setTaxRateCode($row['tax_rate_code']);
        $market->setTaxTypeCode($row['tax_type_code']);
        $market->setAgentId($row['agent_id']);
        $market->setSupplierId($row['supplier_id']);
        $market->setPublicationFormatId($row['publication_format_id']);

        if ($callHooks) {
            Hook::call('MarketDAO::_fromRow', [&$market, &$row]);
        }

        return $market;
    }

    /**
     * Insert a new market entry.
     *
     * @param Market $market
     */
    public function insertObject($market)
    {
        $this->update(
            'INSERT INTO markets
				(publication_format_id, countries_included, countries_excluded, regions_included, regions_excluded, market_date_role, market_date_format, market_date, price, discount, price_type_code, currency_code, tax_rate_code, tax_type_code, agent_id, supplier_id)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $market->getPublicationFormatId(),
                serialize($market->getCountriesIncluded() ? $market->getCountriesIncluded() : []),
                serialize($market->getCountriesExcluded() ? $market->getCountriesExcluded() : []),
                serialize($market->getRegionsIncluded() ? $market->getRegionsIncluded() : []),
                serialize($market->getRegionsExcluded() ? $market->getRegionsExcluded() : []),
                $market->getDateRole(),
                $market->getDateFormat(),
                $market->getDate(),
                $market->getPrice(),
                $market->getDiscount(),
                $market->getPriceTypeCode(),
                $market->getCurrencyCode(),
                $market->getTaxRateCode(),
                $market->getTaxTypeCode(),
                (int) $market->getAgentId(),
                (int) $market->getSupplierId()
            ]
        );

        $market->setId($this->getInsertId());
        return $market->getId();
    }

    /**
     * Update an existing market entry.
     *
     * @param Market $market
     */
    public function updateObject($market)
    {
        $this->update(
            'UPDATE markets
				SET countries_included = ?,
				countries_excluded = ?,
				regions_included = ?,
				regions_excluded = ?,
				market_date_role = ?,
				market_date_format = ?,
				market_date = ?,
				price = ?,
				discount = ?,
				price_type_code = ?,
				currency_code = ?,
				tax_rate_code = ?,
				tax_type_code = ?,
				agent_id = ?,
				supplier_id = ?
			WHERE market_id = ?',
            [
                serialize($market->getCountriesIncluded() ? $market->getCountriesIncluded() : []),
                serialize($market->getCountriesExcluded() ? $market->getCountriesExcluded() : []),
                serialize($market->getRegionsIncluded() ? $market->getRegionsIncluded() : []),
                serialize($market->getRegionsExcluded() ? $market->getRegionsExcluded() : []),
                $market->getDateRole(),
                $market->getDateFormat(),
                $market->getDate(),
                $market->getPrice(),
                $market->getDiscount(),
                $market->getPriceTypeCode(),
                $market->getCurrencyCode(),
                $market->getTaxRateCode(),
                $market->getTaxTypeCode(),
                (int) $market->getAgentId(),
                (int) $market->getSupplierId(),
                (int) $market->getId()
            ]
        );
    }

    /**
     * Delete a market entry by id.
     */
    public function deleteObject(Market $market): int
    {
        return $this->deleteById($market->getId());
    }

    /**
     * Delete a market entry by id.
     */
    public function deleteById(int $entryId): int
    {
        return DB::table('markets')
            ->where('market_id', '=', $entryId)
            ->delete();
    }
}
