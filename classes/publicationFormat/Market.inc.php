<?php

/**
 * @file classes/publicationFormat/Market.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Market
 * @ingroup publicationFormat
 * @see MarketDAO
 *
 * @brief Basic class describing a Market composite type (used on the ONIX templates for publication formats)
 */

class Market extends DataObject {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * get publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->getData('publicationFormatId');
	}

	/**
	 * set publication format id
	 * @param $pressId int
	 */
	function setPublicationFormatId($publicationFormatId) {
		return $this->setData('publicationFormatId', $publicationFormatId);
	}

	/**
	 * Get the included countries for this market entry
	 * @return array
	 */
	function getCountriesIncluded() {
		return $this->getData('countriesIncluded');
	}

	/**
	 * Set the included country list for this market entry
	 * @param $countriesIncluded array
	 */
	function setCountriesIncluded($countriesIncluded) {
		$this->setData('countriesIncluded', array_filter($countriesIncluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the excluded countries for this market entry
	 * @return array
	 */
	function getCountriesExcluded() {
		return $this->getData('countriesExcluded');
	}

	/**
	 * Set the excluded country list for this market entry
	 * @param $countriesExcluded array
	 */
	function setCountriesExcluded($countriesExcluded) {
		$this->setData('countriesExcluded', array_filter($countriesExcluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the included regions for this market entry
	 * @return array
	 */
	function getRegionsIncluded() {
		return $this->getData('regionsIncluded');
	}

	/**
	 * Set the included region list for this market entry
	 * @param $regionsIncluded array
	 */
	function setRegionsIncluded($regionsIncluded) {
		$this->setData('regionsIncluded', array_filter($regionsIncluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the excluded regions for this market entry
	 * @return array
	 */
	function getRegionsExcluded() {
		return $this->getData('regionsExcluded');
	}

	/**
	 * Set the excluded region list for this market entry
	 * @param $regionsExcluded array
	 */
	function setRegionsExcluded($regionsExcluded) {
		$this->setData('regionsExcluded', array_filter($regionsExcluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the date role for this Market.
	 * @return string
	 */
	function getDateRole() {
		return $this->getData('dateRole');
	}

	/**
	 * Set the date role for this Market. (List163)
	 * @param String $dateRole
	 */
	function setDateRole($dateRole) {
		$this->setData('dateRole', $dateRole);
	}

	/**
	 * Get the date format for this Market.
	 * @return string
	 */
	function getDateFormat() {
		return $this->getData('dateFormat');
	}

	/**
	 * Set the date format for this Market. (List55)
	 * @param string $dateFormat
	 */
	function setDateFormat($dateFormat) {
		$this->setData('dateFormat', $dateFormat);
	}

	/**
	 * Get the date for this Market.
	 * @return string
	 */
	function getDate() {
		return $this->getData('date');
	}

	/**
	 * Set this Market's date.
	 * @param string $date
	 */
	function setDate($date) {
		$this->setData('date', $date);
	}

	/**
	 * Get the currency code (ONIX value) used for this market (List96).
	 * @return string
	 */
	function getCurrencyCode() {
		return $this->getData('currencyCode');
	}

	/**
	 * Set the currency code (ONIX value) for a market.
	 * @param string $currencyCode
	 */
	function setCurrencyCode($currencyCode) {
		return $this->setData('currencyCode', $currencyCode);
	}

	/**
	 * Get the price.
	 * @return string
	 */
	function getPrice() {
		return $this->getData('price');
	}

	/**
	 * Set the price.
	 * @param string $price
	 */
	function setPrice($price) {
		return $this->setData('price', $price);
	}

	/**
	 * Get the discount.
	 * @return string
	 */
	function getDiscount() {
		return $this->getData('discount');
	}

	/**
	 * Set the discount.
	 * @param string $discount
	 */
	function setDiscount($discount) {
		return $this->setData('discount', $discount);
	}


	/**
	 * Get the price type code (ONIX code) used for this market (List58).
	 * @return string
	 */
	function getPriceTypeCode() {
		return $this->getData('priceTypeCode');
	}

	/**
	 * Set the price type code (ONIX code) for a market.
	 * @param string $priceTypeCode
	 */
	function setPriceTypeCode($priceTypeCode) {
		return $this->setData('priceTypeCode', $priceTypeCode);
	}

	/**
	 * Get the tax rate code (ONIX value) used for this market (List62).
	 * @return string
	 */
	function getTaxRateCode() {
		return $this->getData('taxRateCode');
	}

	/**
	 * Set the tax rate code (ONIX value) for a market.
	 * @param string $taxRateCode
	 */
	function setTaxRateCode($taxRateCode) {
		return $this->setData('taxRateCode', $taxRateCode);
	}

	/**
	 * Get the tax type code used (ONIX value) for this market (List171).
	 * @return string
	 */
	function getTaxTypeCode() {
		return $this->getData('taxTypeCode');
	}

	/**
	 * Set the tax type code (ONIX value) for a market.
	 * @param string $taxTypeCode
	 */
	function setTaxTypeCode($taxTypeCode) {
		return $this->setData('taxTypeCode', $taxTypeCode);
	}

	/**
	 * Get the id of the assigned agent, if there is one.
	 * @return string
	 */
	function getAgentId() {
		return $this->getData('agentId');
	}

	/**
	 * Set the id of the assigned agent.
	 * @param int $agentId
	 */
	function setAgentId($agentId) {
		return $this->setData('agentId', $agentId);
	}

	/**
	 * Get the id of the assigned supplier, if there is one.
	 * @return string
	 */
	function getSupplierId() {
		return $this->getData('supplierId');
	}

	/**
	 * Set the id of the assigned supplier.
	 * @param int $supplierId
	 */
	function setSupplierId($supplierId) {
		return $this->setData('supplierId', $supplierId);
	}

	/**
	 * Returns a string briefly describing the territories for this market
	 * @return string
	 */
	function getTerritoriesAsString() {
		$territories = __('grid.catalogEntry.included');
		$territories .= ': ' . join(', ', array_merge($this->getCountriesIncluded(), $this->getRegionsIncluded()));
		$territories .= ', ' . __('grid.catalogEntry.excluded');
		$territories .= ': ' . join(', ', array_merge($this->getCountriesExcluded(), $this->getRegionsExcluded()));

		return $territories;
	}

	/**
	 * Returns a string containing the name of the reps assigned to this Market territory.
	 * @return string
	 */
	function getAssignedRepresentativeNames() {
		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$agent = $representativeDao->getById($this->getAgentId());
		$supplier = $representativeDao->getById($this->getSupplierId());

		$returner = '';

		if (isset($agent) && isset($supplier)) {
			$returner = join(', ', array($agent->getName(), $supplier->getName()));
		} else if (isset($agent) && !isset($supplier)) {
			$returner = $agent->getName();
		} else if (isset($supplier)) {
			$returner = $supplier->getName();
		}

		return $returner;
	}

	/**
	 * Internal function for an array_filter to remove empty countries.
	 * array_filter() can be called without a callback to remove empty array elements but it depends
	 * on type juggling and may not be reliable.
	 * @param String $value
	 * @return boolean
	 */
	function _removeEmptyElements($value) {
		return (trim($value) != '') ? true : false;
	}
}


