<?php

/**
 * @file classes/publicationFormat/PublicationFormat.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormat
 * @ingroup publicationFormat
 * @see PublicationFormatDAO
 *
 * @brief Basic class describing a publication format.
 */

class PublicationFormat extends DataObject {
	/**
	 * Constructor
	 */
	function PublicationFormat() {
		parent::DataObject();
	}

	/**
	 * get press id
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * set press id
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * get enabled flag
	 * @return int
	 */
	function getEnabled() {
		return $this->getData('enabled');
	}

	/**
	 * set enabled flag
	 * @param $enabled int
	 */
	function setEnabled($enabled) {
		return $this->setData('enabled', $enabled);
	}

	/**
	 * get physical format flag
	 * @return int
	 */
	function getPhysicalFormat() {
		return $this->getData('physicalFormat');
	}

	/**
	 * set physical format flag
	 * @param $physicalFormat int
	 */
	function setPhysicalFormat($physicalFormat) {
		return $this->setData('physicalFormat', $physicalFormat);
	}

	/**
	 * Set the name of the publication format
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the publication format
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the publication format
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Get the ONIX code for this publication format
	 * @return string
	 */
	function getEntryKey() {
		return $this->getData('entryKey');
	}

	/**
	 * Sets the ONIX code for the publication format
	 * @param string $code
	 */
	function setEntryKey($entryKey) {
		$this->setData('entryKey', $entryKey);
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List7'); // List7 is for object formats
		return $codes[$this->getEntryKey()];
	}
}

?>
