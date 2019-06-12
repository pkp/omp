<?php

/**
 * @defgroup monograph Monographs
 */

/**
 * @file classes/monograph/Monograph.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Monograph
 * @ingroup monograph
 * @see SubmissionDAO
 *
 * @brief Class for a Monograph.
 */

define('WORK_TYPE_EDITED_VOLUME', 1);
define('WORK_TYPE_AUTHORED_WORK', 2);

import('lib.pkp.classes.submission.PKPSubmission');
import('classes.monograph.Author');

class Monograph extends PKPSubmission {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * get press id
	 * @return int
	 */
	function getPressId() {
		return $this->getContextId();
	}

	/**
	 * set press id
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setContextId($pressId);
	}

	/**
	 * Return the "best" monograph ID -- If a public ID is set,
	 * use it; otherwise use the internal ID.
	 * @return string
	 */
	function getBestId() {
		$publicMonographId = $this->getStoredPubId('publisher-id');
		if (!empty($publicMonographId)) return $publicMonographId;
		return $this->getId();
	}

	/**
	 * Get the series id.
	 * @return int
	 */
	function getSeriesId() {
		return $this->getSectionId();
	}

	/**
	 * @see PKPSubmission::getSectionId()
	 */
	function getSectionId() {
		return $this->getData('seriesId');
	}

	/**
	 * Set the series id.
	 * @param $id int
	 */
	function setSeriesId($id) {
		$this->setData('seriesId', $id);
	}

	/**
	 * Get the series's title.
	 * @return string
	 */
	function getSeriesTitle() {
		return $this->getData('seriesTitle');
	}

	/**
	 * Set the series title.
	 * @param $title string
	 */
	function setSeriesTitle($title) {
		$this->setData('seriesTitle', $title);
	}

	/**
	 * Get the series's abbreviated identifier.
	 * @return string
	 */
	function getSeriesAbbrev() {
		return $this->getData('seriesAbbrev');
	}

	/**
	 * Set the series's abbreviated identifier.
	 * @param $abbrev string
	 */
	function setSeriesAbbrev($abbrev) {
		$this->setData('seriesAbbrev', $abbrev);
	}

	/**
	 * Get the position of this monograph within a series.
	 * @return string
	 */
	function getSeriesPosition() {
		return $this->getData('seriesPosition');
	}

	/**
	 * Set the series position for this monograph.
	 * @param $seriesPosition string
	 */
	function setSeriesPosition($seriesPosition) {
		$this->setData('seriesPosition', $seriesPosition);
	}

	/**
	 * Get the work type (constant in WORK_TYPE_...)
	 * @return int
	 */
	function getWorkType() {
		return $this->getData('workType');
	}

	/**
	 * Set the work type (constant in WORK_TYPE_...)
	 * @param $workType int
	 */
	function setWorkType($workType) {
		$this->setData('workType', $workType);
	}

	/**
	 * Get localized supporting agencies array.
	 * @return array
	 */
	function getLocalizedSupportingAgencies() {
		return $this->getLocalizedData('supportingAgencies');
	}

	/**
	 * Get supporting agencies.
	 * @param $locale
	 * @return array
	 */
	function getSupportingAgencies($locale) {
		return $this->getData('supportingAgencies', $locale);
	}

	/**
	 * Set supporting agencies.
	 * @param $supportingAgencies array
	 * @param $locale
	 */
	function setSupportingAgencies($title, $locale) {
		return $this->setData('supportingAgencies', $title, $locale);
	}

	/**
	 * Get whether or not this monograph has metadata approved to
	 * be available in catalog.
	 * @return boolean;
	 */
	function isMetadataApproved() {
		return (boolean) $this->getDatePublished();
	}

	/**
	 * Get the value of a license field from the containing context.
	 * @param $locale string Locale code
	 * @param $field PERMISSIONS_FIELD_...
	 * @return string|null
	 */
	function _getContextLicenseFieldValue($locale, $field) {
		$contextDao = Application::getContextDAO();
		$context = $contextDao->getById($this->getContextId());
		$fieldValue = null; // Scrutinizer
		switch ($field) {
			case PERMISSIONS_FIELD_LICENSE_URL:
				$fieldValue = $context->getSetting('licenseURL');
				break;
			case PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
				switch($context->getSetting('copyrightHolderType')) {
					case 'author':
						$fieldValue = array($context->getPrimaryLocale() => $this->getAuthorString());
						break;
					case 'other':
						$fieldValue = $context->getSetting('copyrightHolderOther');
						break;
					case 'context':
					default:
						$fieldValue = $context->getName(null);
						break;
				}
				break;
			case PERMISSIONS_FIELD_COPYRIGHT_YEAR:
				$fieldValue = date('Y');
				$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
				$publishedSubmission = $publishedSubmissionDao->getById($this->getId());
				if ($publishedSubmission) {
					$fieldValue = date('Y', strtotime($publishedSubmission->getDatePublished()));
				}
				break;
			default: assert(false);
		}

		// Return the fetched license field
		if ($locale === null || !is_array($fieldValue)) return $fieldValue;
		if (isset($fieldValue[$locale])) return $fieldValue[$locale];
		return null;
	}

	/**
	 * get cover page server-side file name
	 * @return string
	 */
	function getCoverImage() {
		return $this->getData('coverImage');
	}

	/**
	 * set cover page server-side file name
	 * @param $coverImage string
	 */
	function setCoverImage($coverImage) {
		$this->setData('coverImage', $coverImage);
	}

	/**
	 * get cover page alternate text
	 * @return string
	 */
	function getCoverImageAltText() {
		return $this->getData('coverImageAltText');
	}

	/**
	 * set cover page alternate text
	 * @param $coverImageAltText string
	 */
	function setCoverImageAltText($coverImageAltText) {
		$this->setData('coverImageAltText', $coverImageAltText);
	}
}

?>
