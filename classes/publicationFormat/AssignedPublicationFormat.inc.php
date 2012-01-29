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
	 * Set ID of assigned format.
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
	 * set monograph id.
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
	 * Get the country of manufacture code that this format was manufactured in.
	 * @return string
	 */
	function getCountryManufactureCode() {
		return $this->getData('countryManufactureCode');
	}

	/**
	 * Set the country of manufacture code for a publication format.
	 * @param string $countryManufactureCode
	 */
	function setCountryManufactureCode($countryManufactureCode) {
		return $this->setData('countryManufactureCode', $countryManufactureCode);
	}

	/**
	 * Get the product availability code (ONIX value) for this format (List65).
	 * @return string
	 */
	function getProductAvailabilityCode() {
		return $this->getData('productAvailabilityCode');
	}

	/**
	 * Set the product availability code (ONIX value) for a publication format.
	 * @param string $productAvailabilityCode
	 */
	function setProductAvailabilityCode($productAvailabilityCode) {
		return $this->setData('productAvailabilityCode', $productAvailabilityCode);
	}

	/**
	 * Get the height of the monograph format.
	 * @return string
	 */
	function getHeight() {
		return $this->getData('height');
	}

	/**
	 * Set the height of a publication format.
	 * @param string $height
	 */
	function setHeight($height) {
		return $this->setData('height', $height);
	}

	/**
	 * Get the height unit (ONIX value) of the monograph format (List50).
	 * @return string
	 */
	function getHeightUnitCode() {
		return $this->getData('heightUnitCode');
	}

	/**
	 * Set the height unit (ONIX value) for a publication format.
	 * @param string $heightUnitCode
	 */
	function setHeightUnitCode($heightUnitCode) {
		return $this->setData('heightUnitCode', $heightUnitCode);
	}

	/**
	 * Get the width of the monograph format.
	 * @return string
	 */
	function getWidth() {
		return $this->getData('width');
	}

	/**
	 * Set the width of a publication format.
	 * @param string $width
	 */
	function setWidth($width) {
		return $this->setData('width', $width);
	}

	/**
	 * Get the width unit code (ONIX value) of the monograph format (List50).
	 * @return string
	 */
	function getWidthUnitCode() {
		return $this->getData('widthUnitCode');
	}

	/**
	 * Set the width unit code (ONIX value) for a publication format.
	 * @param string $widthUnitCode
	 */
	function setWidthUnitCode($widthUnitCode) {
		return $this->setData('widthUnitCode', $widthUnitCode);
	}

	/**
	 * Get the thickness of the monograph format.
	 * @return string
	 */
	function getThickness() {
		return $this->getData('thickness');
	}

	/**
	 * Set the thickness of a publication format.
	 * @param string $thinkness
	 */
	function setThickness($thickness) {
		return $this->setData('thickness', $thickness);
	}

	/**
	 * Get the thickness unit code (ONIX value) of the monograph format (List50).
	 * @return string
	 */
	function getThicknessUnitCode() {
		return $this->getData('thicknessUnitCode');
	}

	/**
	 * Set the thickness unit code (ONIX value) for a publication format.
	 * @param string $thicknessUnitCode
	 */
	function setThicknessUnitCode($thicknessUnitCode) {
		return $this->setData('thicknessUnitCode', $thicknessUnitCode);
	}

	/**
	 * Get the weight of the monograph format.
	 * @return string
	 */
	function getWeight() {
		return $this->getData('weight');
	}

	/**
	 * Set the weight for a publication format.
	 * @param string $weight
	 */
	function setWeight($weight) {
		return $this->setData('weight', $weight);
	}

	/**
	 * Get the weight unit code (ONIX value) of the monograph format (List95).
	 * @return string
	 */
	function getWeightUnitCode() {
		return $this->getData('weightUnitCode');
	}

	/**
	 * Set the weight unit code (ONIX value) for a publication format.
	 * @param string $weightUnitCode
	 */
	function setWeightUnitCode($weightUnitCode) {
		return $this->setData('weightUnitCode', $weightUnitCode);
	}

	/**
	 * Get the file size of the monograph format.
	 * @return string
	 */
	function getFileSize() {
		return $this->getData('fileSize');
	}

	/**
	 * Set the file size of the publication format.
	 * @param string $fileSize
	 */
	function setFileSize($fileSize) {
		return $this->setData('fileSize', $fileSize);
	}

	/**
	 * Get the SalesRights objects for this format.
	 * @return Array SalesRights
	 */
	function getSalesRights() {
		$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
		$salesRights =& $salesRightsDao->getByAssignedPublicationFormatId($this->getAssignedPublicationFormatId());
		return $salesRights;
	}

	/**
	 * Get the IdentificationCode objects for this format.
	 * @return Array IdentificationCode
	 */
	function getIdentificationCodes() {
		$identificationCodeDao =& DAORegistry::getDAO('IdentificationCodeDAO');
		$codes =& $identificationCodeDao->getByAssignedPublicationFormatId($this->getAssignedPublicationFormatId());
		return $codes;
	}

	/**
	 * Get the PublicationDate objects for this format.
	 * @return Array PublicationDate
	 */
	function getPublicationDates() {
		$publicationDateDao =& DAORegistry::getDAO('PublicationDateDAO');
		$dates =& $publicationDateDao->getByAssignedPublicationFormatId($this->getAssignedPublicationFormatId());
		return $dates;
	}

	/**
	 * Get the Market objects for this format.
	 * @return Array Market
	 */
	function getMarkets() {
		$marketDao =& DAORegistry::getDAO('MarketDAO');
		$markets =& $marketDao->getByAssignedPublicationFormatId($this->getAssignedPublicationFormatId());
		return $markets;
	}
	/**
	 * Get the product form detail code (ONIX value) for the format used for this format (List151).
	 * @return string
	 */
	function getProductFormDetailCode() {
		return $this->getData('productFormDetailCode');
	}

	/**
	 * Set the product form detail code (ONIX value) for a publication format.
	 * @param string $productFormDetailCode
	 */
	function setProductFormDetailCode($productFormDetailCode) {
		return $this->setData('productFormDetailCode', $productFormDetailCode);
	}

	/**
	 * Get the product composition code (ONIX value) used for this format (List2).
	 * @return string
	 */
	function getProductCompositionCode() {
		return $this->getData('productCompositionCode');
	}

	/**
	 * Set the product composition code (ONIX value) for a publication format.
	 * @param string $productCompositionCode
	 */
	function setProductCompositionCode($productCompositionCode) {
		return $this->setData('productCompositionCode', $productCompositionCode);
	}

	/**
	 * Get the page count for the front matter section of a publication format.
	 * @return string
	 */
	function getFrontMatter() {
		return $this->getData('frontMatter');
	}

	/**
	 * Set the front matter page count for a publication format.
	 * @param string $frontMatter
	 */
	function setFrontMatter($frontMatter) {
		return $this->setData('frontMatter', $frontMatter);
	}

	/**
	 * Get the page count for the back matter section of a publication format.
	 * @return string
	 */
	function getBackMatter() {
		return $this->getData('backMatter');
	}

	/**
	 * Set the back matter page count for a publication format.
	 * @param string $backMatter
	 */
	function setBackMatter($backMatter) {
		return $this->setData('backMatter', $backMatter);
	}

	/**
	 * Get the imprint brand name for a publication format.
	 * @return string
	 */
	function getImprint() {
		return $this->getData('imprint');
	}

	/**
	 * Set the imprint brand name for a publication format.
	 * @param string $imprint
	 */
	function setImprint($imprint) {
		return $this->setData('imprint', $imprint);
	}

	/**
	 * Get the technical protection code for a digital publication format (List144).
	 * @return string
	 */
	function getTechnicalProtectionCode() {
		return $this->getData('technicalProtectionCode');
	}

	/**
	 * Set the technical protection code for a publication format.
	 * @param string $technicalProtectionCode
	 */
	function setTechnicalProtectionCode($technicalProtectionCode) {
		return $this->setData('technicalProtectionCode', $technicalProtectionCode);
	}

	/**
	 * Get the return code for a physical publication format (List66).
	 * @return string
	 */
	function getReturnableIndicatorCode() {
		return $this->getData('returnableIndicatorCode');
	}

	/**
	 * Set the return code for a publication format.
	 * @param string $returnableIndicatorCode
	 */
	function setReturnableIndicatorCode($returnableIndicatorCode) {
		return $this->setData('returnableIndicatorCode', $returnableIndicatorCode);
	}
}
?>