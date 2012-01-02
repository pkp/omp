<?php

/**
 * @file classes/publicationFormat/AssignedPublicationFormat.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AssignedPublicationFormat
 * @ingroup publicationFormat
 * @see AssignedPublicationFormatDAO
 *
 * @brief A publication format that has been assigned to a published monograph.
 */


import('classes.publicationFormat.PublicationFormat');


class AssignedPublicationFormat extends PublicationFormat {

	/**
	 * Constructor.
	 */
	function AssignedPublicationFormat() {
		parent::PublicationFormat();
	}

	/**
	 * Get ID of assigned format.
	 * @return int
	 */
	function getAssignedPublicationFormatId() {
		return $this->getData('assignedPublicationFormatId');
	}

	/**
	 * Set ID of assigned format
	 * @param $id int
	 */
	function setAssignedPublicationFormatId($assignedPublicationFormatId) {
		return $this->setData('assignedPublicationFormatId', $assignedPublicationFormatId);
	}

	/**
	 * Get sequence of format in format listings for the monograph.
	 * @return float
	 */
	function getSeq() {
		return $this->getData('seq');
	}

	/**
	 * Set sequence of format in format listings for the monograph.
	 * @param $sequence float
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

	/**
	 * Get "localized" format title (if applicable).
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get the format title (if applicable).
	 * @return string
	 */
	function getTitle() {
		return $this->getData('title');
	}
	/**
	 * Set title.
	 * @param $title string
	 * @param $locale
	 */
	function setTitle($title) {
		return $this->setData('title', $title);
	}

	/**
	 * set monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * get monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Get the country codes for this publication format's distribution range.
	 * @return array of strings
	 */
	function getDistributionCountries() {
		return $this->getData('countriesIncludedCode');
	}

	/**
	 * Get the countries for this publication format, space separated.
	 * @return string
	 */
	function getDistributionCountriesAsString() {
		$countries =& $this->getDistributionCountries();
		if (is_array($countries)) {
			return join(' ', $countries);
		} else {
			return $countries;
		}
	}

	/**
	 * Get the height of the monograph format
	 * @return string
	 */
	function getHeight() {
		return $this->getData('height');
	}

	/**
	 * Get the height unit (ONIX value) of the monograph format
	 * @return string
	 */
	function getHeightUnit() {
		return $this->getData('heightUnitCode');
	}

	/**
	 * Get the width of the monograph format
	 * @return string
	 */
	function getWidth() {
		return $this->getData('width');
	}

	/**
	 * Get the width unit (ONIX value) of the monograph format
	 * @return string
	 */
	function getWidthUnit() {
		return $this->getData('widthUnitCode');
	}

	/**
	 * Get the thickness of the monograph format
	 * @return string
	 */
	function getThickness() {
		return $this->getData('thickness');
	}

	/**
	 * Get the thickness unit (ONIX value) of the monograph format
	 * @return string
	 */
	function getThicknessUnit() {
		return $this->getData('thicknessUnitCode');
	}

	/**
	 * Get the weight of the monograph format
	 * @return string
	 */
	function getWeight() {
		return $this->getData('weight');
	}

	/**
	 * Get the weight unit (ONIX value) of the monograph format
	 * @return string
	 */
	function getWeightUnit() {
		return $this->getData('weightUnitCode');
	}

	/**
	 * Get the file size, if applicable, of the monograph format
	 * @return string
	 */
	function getFileSize() {
		return $this->getData('fileSize');
	}

	/**
	 * Get the Identifier (ISBN value, etc) for this format
	 * @return string
	 */
	function getProductIdentifier() {
		return $this->getData('productIdentifier');
	}

	/**
	 * Get the ONIX code for the identifier used for this format
	 * @return string
	 */
	function getProductIdentifierTypeCode() {
		return $this->getData('productIdentifierTypeCode');
	}
}
?>