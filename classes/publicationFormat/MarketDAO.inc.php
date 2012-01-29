<?php

/**
 * @file classes/publicationFormat/MarketDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	function MarketDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a market entry by type id.
	 * @param $marketId int
	 * @param $monographId optional int
	 * @return Market
	 */
	function &getById($marketId, $monographId = null){
		$sqlParams = array((int) $marketId);
		if ($monographId) {
			$sqlParams[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT m.*
				FROM markets m
			JOIN published_monograph_publication_formats pmpf ON (m.assigned_publication_format_id = pmpf.assigned_publication_format_id)
			WHERE m.market_id = ?
				' . ($monographId?' AND pmpf.monograph_id = ?':''),
			$sqlParams);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all market for an assigned publication format
	 * @param $assignedPublicationFormatId int
	 * @return DAOResultFactory containing matching market.
	 */
	function &getByAssignedPublicationFormatId($assignedPublicationFormatId) {
		$result =& $this->retrieveRange(
			'SELECT * FROM markets WHERE assigned_publication_format_id = ?', array((int) $assignedPublicationFormatId));

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
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
	function &_fromRow(&$row, $callHooks = true) {
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
		$market->setAssignedPublicationFormatId($row['assigned_publication_format_id']);

		if ($callHooks) HookRegistry::call('MarketDAO::_fromRow', array(&$market, &$row));

		return $market;
	}

	/**
	 * Insert a new market entry.
	 * @param $market Market
	 */
	function insertObject(&$market) {
		$this->update(
			'INSERT INTO markets
				(assigned_publication_format_id, countries_included, countries_excluded, regions_included, regions_excluded, market_date_role, market_date_format, market_date, price, discount, price_type_code, currency_code, tax_rate_code, tax_type_code, agent_id, supplier_id)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $market->getAssignedPublicationFormatId(),
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

		$market->setId($this->getInsertMarketId());
		return $market->getId();
	}

	/**
	 * Update an existing market entry.
	 * @param $market Market
	 */
	function updateObject(&$market) {
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
	 * delete a market entry by id.
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
	function getInsertMarketId() {
		return $this->getInsertId('markets', 'market_id');
	}
}

?>
