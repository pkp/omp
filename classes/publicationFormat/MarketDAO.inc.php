<?php

/**
 * @file classes/publicationFormat/MarketDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarketDAO
 * @ingroup publicationFormat
 * @see Market
 *
 * @brief Operations for retrieving and modifying Market objects.
 */

import('classes.publicationFormat.Market');

class MarketDAO extends DAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a market entry by type id.
	 * @param $marketId int
	 * @param $publicationId optional int
	 * @return Market
	 */
	function getById($marketId, $publicationId = null){
		$sqlParams = array((int) $marketId);
		if ($publicationId) {
			$sqlParams[] = (int) $publicationId;
		}

		$result = $this->retrieve(
			'SELECT	m.*
			FROM	markets m
				JOIN publication_formats pf ON (m.publication_format_id = pf.publication_format_id)
			WHERE	m.market_id = ?
				' . ($publicationId?' AND pf.publication_id = ?':''),
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
	 * Retrieve all market for a publication format
	 * @param $publicationFormatId int
	 * @return DAOResultFactory containing matching market.
	 */
	function getByPublicationFormatId($publicationFormatId) {
		$result = $this->retrieveRange(
			'SELECT * FROM markets WHERE publication_format_id = ?', (int) $publicationFormatId);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Market
	 */
	function newDataObject() {
		return new Market();
	}

	/**
	 * Internal function to return a Market object from a row.
	 * @param $row array
	 * @param $callHooks boolean
	 * @return Market
	 */
	function _fromRow($row, $callHooks = true) {
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

		if ($callHooks) HookRegistry::call('MarketDAO::_fromRow', array(&$market, &$row));

		return $market;
	}

	/**
	 * Insert a new market entry.
	 * @param $market Market
	 */
	function insertObject($market) {
		$this->update(
			'INSERT INTO markets
				(publication_format_id, countries_included, countries_excluded, regions_included, regions_excluded, market_date_role, market_date_format, market_date, price, discount, price_type_code, currency_code, tax_rate_code, tax_type_code, agent_id, supplier_id)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $market->getPublicationFormatId(),
				serialize($market->getCountriesIncluded() ? $market->getCountriesIncluded() : array()),
				serialize($market->getCountriesExcluded() ? $market->getCountriesExcluded() : array()),
				serialize($market->getRegionsIncluded() ? $market->getRegionsIncluded() : array()),
				serialize($market->getRegionsExcluded() ? $market->getRegionsExcluded() : array()),
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
			)
		);

		$market->setId($this->getInsertId());
		return $market->getId();
	}

	/**
	 * Update an existing market entry.
	 * @param $market Market
	 */
	function updateObject($market) {
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
			array(
				serialize($market->getCountriesIncluded() ? $market->getCountriesIncluded() : array()),
				serialize($market->getCountriesExcluded() ? $market->getCountriesExcluded() : array()),
				serialize($market->getRegionsIncluded() ? $market->getRegionsIncluded() : array()),
				serialize($market->getRegionsExcluded() ? $market->getRegionsExcluded() : array()),
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
			)
		);
	}

	/**
	 * Delete a market entry by id.
	 * @param $market Market
	 */
	function deleteObject($market) {
		return $this->deleteById($market->getId());
	}

	/**
	 * Delete a market entry by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'DELETE FROM markets WHERE market_id = ?', array((int) $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted market entry.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('markets', 'market_id');
	}
}


