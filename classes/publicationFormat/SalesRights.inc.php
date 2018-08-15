<?php

/**
 * @file classes/publicationFormat/SalesRights.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SalesRights
 * @ingroup publicationFormat
 * @see SalesRightsDAO
 *
 * @brief Basic class describing a sales rights composite type (used on the ONIX templates for publication formats)
 */

class SalesRights extends DataObject {
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
	 * Set the ONIX code for this sales rights entry
	 * @param $type string
	 */
	function setType($type) {
		$this->setData('type', $type);
	}

	/**
	 * Get the ONIX code for this sales rights entry
	 * @return string
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List46'); // List46 is for things like 'unrestricted sale with exclusive rights', etc.
		return $codes[$this->getType()];
	}

	/**
	 * Set the ROWSetting for this sales rights entry (Rest Of World)
	 * @param $rowSetting boolean
	 */
	function setROWSetting($rowSetting) {
		$this->setData('rowSetting', $rowSetting);
	}

	/**
	 * Get the ROWSetting value for this sales rights entry (Rest Of World)
	 * @return string
	 */
	function getROWSetting() {
		return $this->getData('rowSetting');
	}

	/**
	 * Get the included countries for this sales rights entry
	 * @return array
	 */
	function getCountriesIncluded() {
		return $this->getData('countriesIncluded');
	}

	/**
	 * Set the included country list for this sales rights entry
	 * @param $countriesIncluded array
	 */
	function setCountriesIncluded($countriesIncluded) {
		$this->setData('countriesIncluded', array_filter($countriesIncluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the excluded countries for this sales rights entry
	 * @return array
	 */
	function getCountriesExcluded() {
		return $this->getData('countriesExcluded');
	}

	/**
	 * Set the excluded country list for this sales rights entry
	 * @param $countriesExcluded array
	 */
	function setCountriesExcluded($countriesExcluded) {
		$this->setData('countriesExcluded', array_filter($countriesExcluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the included regions for this sales rights entry
	 * @return array
	 */
	function getRegionsIncluded() {
		return $this->getData('regionsIncluded');
	}

	/**
	 * Set the included region list for this sales rights entry
	 * @param $regionsIncluded array
	 */
	function setRegionsIncluded($regionsIncluded) {
		$this->setData('regionsIncluded', array_filter($regionsIncluded, array(&$this, '_removeEmptyElements')));
	}

	/**
	 * Get the excluded regions for this sales rights entry
	 * @return array
	 */
	function getRegionsExcluded() {
		return $this->getData('regionsExcluded');
	}

	/**
	 * Set the excluded region list for this sales rights entry
	 * @param $regionsExcluded array
	 */
	function setRegionsExcluded($regionsExcluded) {
		$this->setData('regionsExcluded', array_filter($regionsExcluded, array(&$this, '_removeEmptyElements')));
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


