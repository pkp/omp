<?php

/**
 * @file classes/publicationFormat/AssignedPublicationFormat.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	 * Get the country codes for this publication format's distribution range. (List91)
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
	 * Get the height unit (ONIX value) of the monograph format (List50)
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
	 * Get the width unit (ONIX value) of the monograph format (List50)
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
	 * Get the thickness unit (ONIX value) of the monograph format (List50)
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
	 * Get the weight unit (ONIX value) of the monograph format (List95)
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
	 * Get the IdentificationCode objects for this format
	 * @return Array IdentificationCode
	 */
	function getIdentificationCodes() {
		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$codes =& $identificationCodeDao->getByAssignedPublicationFormatId($this->getAssignedPublicationFormatId());
		return $codes;
	}

	/**
	 * Get the ONIX code for the format used for this format (List7)
	 * @return string
	 */
	function getProductFormCode() {
		return $this->getData('productFormCode');
	}

	/**
	 * Get the ONIX code for the format used for this format (List151)
	 * @return string
	 */
	function getProductFormDetailCode() {
		return $this->getData('productFormDetailCode');
	}

	/**
	 * Get the ONIX code for the composition used for this format (List2)
	 * @return string
	 */
	function getProductCompositionCode() {
		return $this->getData('productCompositionCode');
	}

	/**
	 * Get the ONIX code for the currency code used for this format (List96)
	 * @return string
	 */
	function getCurrencyCode() {
		return $this->getData('currencyCode');
	}

	/**
	 * Get the price
	 * @return string
	 */
	function getPrice() {
		return $this->getData('price');
	}

	/**
	 * Get the ONIX code for the price type code used for this format (List58)
	 * @return string
	 */
	function getPriceTypeCode() {
		return $this->getData('priceTypeCode');
	}

	/**
	 * Get the ONIX code for the tax rate code used for this format (List62)
	 * @return string
	 */
	function getTaxRateCode() {
		return $this->getData('taxRateCode');
	}

	/**
	 * Get the ONIX code for the tax type code used for this format (List171)
	 * @return string
	 */
	function getTaxTypeCode() {
		return $this->getData('taxTypeCode');
	}

	/**
	 * Get the page count for the front matter section of a publication format
	 * @return string
	 */
	function getFrontMatterPageCount() {
		return $this->getData('frontMatter');
	}

	/**
	 * Get the page count for the back matter section of a publication format
	 * @return string
	 */
	function getBackMatterPageCount() {
		return $this->getData('backMatter');
	}
}
?>