<?php

/**
 * @defgroup cataloguingMetadata
 */

/**
 * @file classes/cataloguingMetadata/CataloguingMetadataField.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CataloguingMetadataField
 * @ingroup cataloguingMetadata
 * @see CataloguingMetadataFieldDAO
 *
 * @brief Basic class describing a cataloguing metadata field.
 */

class CataloguingMetadataField extends DataObject {
	/**
	 * Constructor
	 */
	function CataloguingMetadataField() {
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
	 * Set the name of the cataloguing metadata field
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the cataloguing metadata field
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the cataloguing metadata field
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}
}

?>
