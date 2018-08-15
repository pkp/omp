<?php

/**
 * @file classes/publicationFormat/PublicationFormat.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormat
 * @ingroup publicationFormat
 * @see PublicationFormatDAO
 *
 * @brief A publication format for a monograph.
 */

import('lib.pkp.classes.submission.Representation');

class PublicationFormat extends Representation {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Return the "best" publication format ID -- If a public ID is set,
	 * use it; otherwise use the internal ID.
	 * @return string
	 */
	function getBestId() {
		$publicationFormatId = $this->getStoredPubId('publisher-id');
		if (!empty($publicationFormatId)) return $publicationFormatId;
		return $this->getId();
	}

	/**
	 * get physical format flag
	 * @return bool
	 */
	function getPhysicalFormat() {
		return $this->getData('physicalFormat');
	}

	/**
	 * set physical format flag
	 * @param $physicalFormat bool
	 */
	function setPhysicalFormat($physicalFormat) {
		return $this->setData('physicalFormat', $physicalFormat);
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
	 * @param $code string
	 */
	function setEntryKey($entryKey) {
		$this->setData('entryKey', $entryKey);
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes = $onixCodelistItemDao->getCodes('List7'); // List7 is for object formats
		return $codes[$this->getEntryKey()];
	}

	/**
	 * Set monograph id.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return parent::setSubmissionId($monographId);
	}

	/**
	 * Get monograph id
	 * @return int
	 */
	function getMonographId() {
		return parent::getSubmissionId();
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
	 * @param $countryManufactureCode string
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
	 * @param $productAvailabilityCode string
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
	 * @param $height string
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
	 * @param $heightUnitCode string
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
	 * @param $width string
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
	 * @param $widthUnitCode string
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
	 * @param $thickness string
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
	 * @param $thicknessUnitCode string
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
	 * @param $weight string
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
	 * @param $weightUnitCode string
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
	 * Get the file size of the monograph format based on calculated sizes
	 * for approved proof files.
	 * @return string
	 */
	function getCalculatedFileSize() {
		$fileSize = 0;
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // File constants
		$stageMonographFiles = $submissionFileDao->getLatestRevisionsByAssocId(
			ASSOC_TYPE_PUBLICATION_FORMAT, $this->getId(),
			$this->getMonographId(), SUBMISSION_FILE_PROOF
		);

		foreach ($stageMonographFiles as $monographFile) {
			if ($monographFile->getViewable()) {
				$fileSize += (int) $monographFile->getFileSize();
			}
		}

		return sprintf('%d.3', $fileSize/(1024*1024)); // bytes to Mb
	}

	/**
	 * Set the file size of the publication format.
	 * @param $fileSize string
	 */
	function setFileSize($fileSize) {
		return $this->setData('fileSize', $fileSize);
	}

	/**
	 * Get the SalesRights objects for this format.
	 * @return DAOResultFactory SalesRights
	 */
	function getSalesRights() {
		$salesRightsDao = DAORegistry::getDAO('SalesRightsDAO');
		return $salesRightsDao->getByPublicationFormatId($this->getId());
	}

	/**
	 * Get the IdentificationCode objects for this format.
	 * @return DAOResultFactory IdentificationCode
	 */
	function getIdentificationCodes() {
		$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
		return $identificationCodeDao->getByPublicationFormatId($this->getId());
	}

	/**
	 * Get the PublicationDate objects for this format.
	 * @return Array PublicationDate
	 */
	function getPublicationDates() {
		$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
		return $publicationDateDao->getByPublicationFormatId($this->getId());
	}

	/**
	 * Get the Market objects for this format.
	 * @return DAOResultFactory Market
	 */
	function getMarkets() {
		$marketDao = DAORegistry::getDAO('MarketDAO');
		return $marketDao->getByPublicationFormatId($this->getId());
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
	 * @param $productFormDetailCode string
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
	 * @param $productCompositionCode string
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
	 * @param $frontMatter string
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
	 * @param $backMatter string
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
	 * @param $imprint string
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
	 * @param $technicalProtectionCode string
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
	 * @param $returnableIndicatorCode string
	 */
	function setReturnableIndicatorCode($returnableIndicatorCode) {
		return $this->setData('returnableIndicatorCode', $returnableIndicatorCode);
	}

	/**
	 * Get whether or not this format is available in the catalog.
	 * @return int
	 */
	function getIsAvailable() {
		return $this->getData('isAvailable');
	}

	/**
	 * Set whether or not this format is available in the catalog.
	 * @param $isAvailable int
	 */
	function setIsAvailable($isAvailable) {
		return $this->setData('isAvailable', $isAvailable);
	}

	/**
	 * Check to see if this publication format has everything it needs for valid ONIX export
	 * Ideally, do this with a DOMDocument schema validation. We do it this way for now because
	 * of a potential issue with libxml2:  http://stackoverflow.com/questions/6284827
	 *
	 * @return String
	 */
	function hasNeededONIXFields() {
		// ONIX requires one identification code and a market region with a defined price.
		$assignedIdentificationCodes = $this->getIdentificationCodes();
		$assignedMarkets = $this->getMarkets();

		$errors = array();
		if ($assignedMarkets->wasEmpty()) {
			$errors[] = 'monograph.publicationFormat.noMarketsAssigned';
		}

		if ($assignedIdentificationCodes->wasEmpty()) {
			$errors[] = 'monograph.publicationFormat.noCodesAssigned';
		}

		return array_merge($errors, $this->_checkRequiredFieldsAssigned());
	}

	/**
	 * Internal function to provide some validation for the ONIX export by
	 * checking the required ONIX fields associated with this format.
	 * @return array
	 */
	function _checkRequiredFieldsAssigned() {
		$requiredFields = array('productCompositionCode' => 'grid.catalogEntry.codeRequired', 'productAvailabilityCode' => 'grid.catalogEntry.productAvailabilityRequired');

		$errors = array();

		foreach ($requiredFields as $field => $errorCode) {
			if ($this->getData($field) == '') {
				$errors[] = $errorCode;
			}
		}

		if (!$this->getPhysicalFormat()) {
			if (!$this->getFileSize() && !$this->getCalculatedFileSize()) {
				$errors['fileSize'] = 'grid.catalogEntry.fileSizeRequired';
			}
		}

		return $errors;
	}

	/**
	 * Get the press id from the monograph assigned to this publication format.
	 * @return int
	 */
	function getPressId() {
		return $this->getContextId();
	}

	/**
	 * Return the format's physical dimensions
	 * @return string
	 */
	function getDimensions() {

		if (!$this->getPhysicalFormat()) {
			return '';
		}

		$width = $this->getWidth();
		$height = $this->getHeight();
		$thickness = $this->getThickness();

		$dimensions = array();
		if (!empty($width)) { $dimensions[] = $width . $this->getWidthUnitCode(); }
		if (!empty($height)) { $dimensions[] = $height . $this->getHeightUnitCode(); }
		if (!empty($thickness)) { $dimensions[] = $thickness . $this->getThicknessUnitCode(); }

		return join( __('monograph.publicationFormat.productDimensionsSeparator'), $dimensions );
	}
}


