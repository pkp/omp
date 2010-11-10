<?php

/**
 * @defgroup publicationFormat
 */

/**
 * @file classes/publicationFormat/PublicationFormat.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	 * Set the name of the monograph file type
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		$this->setData('name', $name, $locale);
	}

	/**
	 * Get the name of the monograph file type
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Get the localized name of the monograph file type
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Set the designation of the monograph file type
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setDesignation($abbrev, $locale) {
		$this->setData('designation', $abbrev, $locale);
	}

	/**
	 * Get the designation of the monograph file type
	 * @param $locale string
	 * @return string
	 */
	function getDesignation($locale) {
		return $this->getData('designation', $locale);
	}

	/**
	 * Get the localized designation of the monograph file type
	 * @return string
	 */
	function getLocalizedDesignation() {
		return $this->getLocalizedData('designation');
	}
}

?>