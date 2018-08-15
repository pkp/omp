<?php

/**
 * @file classes/monograph/PublishedMonograph.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonograph
 * @ingroup monograph
 * @see PublishedMonographDAO
 *
 * @brief Published monograph class.
 */


import('classes.monograph.Monograph');

// Access status
define('ARTICLE_ACCESS_ISSUE_DEFAULT', 0);
define('ARTICLE_ACCESS_OPEN', 1);

class PublishedMonograph extends Monograph {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get views of the published monograph.
	 * @return int
	 */
	function getViews() {
		$application = Application::getApplication();
		return $application->getPrimaryMetricByAssoc(ASSOC_TYPE_MONOGRAPH, $this->getId());
	}

	/**
	 * Set views of the published monograph.
	 * @param $views int
	 */
	function setViews($views) {
		return $this->setData('views', $views);
	}

	/**
	 * Get the audience of the published monograph.
	 * @return int
	 */
	function getAudience() {
		return $this->getData('audience');
	}

	/**
	 * Set the audience for the published monograph.
	 * @param $audience int (onix code)
	 */
	function setAudience($audience) {
		return $this->setData('audience', $audience);
	}

	/**
	 * Get the audienceRangeQualifier of the published monograph.
	 * @return int
	 */
	function getAudienceRangeQualifier() {
		return $this->getData('audienceRangeQualifier');
	}

	/**
	 * Set the audienceRangeQualifier for the published monograph.
	 * @param $audienceRangeQualifier int (onix code)
	 */
	function setAudienceRangeQualifier($audienceRangeQualifier) {
		return $this->setData('audienceRangeQualifier', $audienceRangeQualifier);
	}

	/**
	 * Get the audienceRangeFrom field for the published monograph.
	 * @return int
	 */
	function getAudienceRangeFrom() {
		return $this->getData('audienceRangeFrom');
	}

	/**
	 * Set the audienceRangeFrom field for the published monograph.
	 * @param $audienceRangeFrom int (onix code)
	 */
	function setAudienceRangeFrom($audienceRangeFrom) {
		return $this->setData('audienceRangeFrom', $audienceRangeFrom);
	}

	/**
	 * Get the audienceRangeTo field for the published monograph.
	 * @return int
	 */
	function getAudienceRangeTo() {
		return $this->getData('audienceRangeTo');
	}

	/**
	 * Set the audienceRangeTo field for the published monograph.
	 * @param $audienceRangeTo int (onix code)
	 */
	function setAudienceRangeTo($audienceRangeTo) {
		return $this->setData('audienceRangeTo', $audienceRangeTo);
	}

	/**
	 * Get the audienceRangeExact field of the published monograph.
	 * @return int
	 */
	function getAudienceRangeExact() {
		return $this->getData('audienceRangeExact');
	}

	/**
	 * Set the audienceRangeExact field for the published monograph.
	 * @param $audienceRangeExact int (onix code)
	 */
	function setAudienceRangeExact($audienceRangeExact) {
		return $this->setData('audienceRangeExact', $audienceRangeExact);
	}

	/**
	 * Retrieves the assigned publication formats for this submission
	 * @param $onlyApproved boolean whether to fetch only those that are approved for publication.
	 * @return array PublicationFormat
	 */
	function getPublicationFormats($onlyApproved = false) {
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		if ($onlyApproved) {
			$formats = $publicationFormatDao->getApprovedBySubmissionId($this->getId());
		} else {
			$formats = $publicationFormatDao->getBySubmissionId($this->getId());
		}
		return $formats->toArray();
	}

	/**
	 * Return string of approved publication formats, separated by comma.
	 * @return string
	 */
	function getPublicationFormatString() {
		$separator = ', ';
		$formats = $this->getPublicationFormats(true);
		$str = '';

		foreach ($formats as $format) { /* @var $format PublicationFormat */
			if (!empty($str)) {
				$str .= $separator;
			}
			$str .= $format->getLocalizedName();
		}

		return $str;
	}

	/**
	 * Returns whether or not this published monograph has formats assigned to it
	 * @return boolean
	 */
	function hasPublicationFormats() {
		$formats = $this->getPublicationFormats();
		return (sizeof($formats) > 0);
	}

	/**
	 * Get the categories for this published monograph.
	 * @see PublishedMonographDAO::getCategories
	 * @return Iterator
	 */
	function getCategories() {
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		return $publishedMonographDao->getCategories(
			$this->getId(),
			$this->getPressId()
		);
	}

	/**
	 * Get whether or not this monograph is available in the catalog.
	 * A monograph is available if it has at least one publication format that
	 * has been flagged as 'available' in the catalog and if it has metadata
	 * approved.
	 * @return boolean
	 */
	function isAvailable() {
		$publicationFormats = $this->getPublicationFormats(true);
		if (sizeof($publicationFormats) > 0 && $this->isMetadataApproved()) {
			return true;
		}
		return false;
	}

	/**
	 * Get the Representative objects assigned as suppliers for this published monograph.
	 * @return Array Representative
	 */
	function getSuppliers() {
		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		return $representativeDao->getSuppliersByMonographId($this->getId());
	}

	/**
	 * Get the Representative objects assigned as agents for this published monograph.
	 * @return Array Representative
	 */
	function getAgents() {
		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		return $representativeDao->getAgentsByMonographId($this->getId());
	}

	/**
	 * Get a string indicating all authors or, if it is an edited volume, editors.
	 * @param $preferred boolean If the preferred public name should be used, if exist
	 * @return string
	 */
	public function getAuthorOrEditorString($preferred = true) {

		if ($this->getWorkType() != WORK_TYPE_EDITED_VOLUME) {
			return $this->getAuthorString($preferred);
		}

		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);

		$authors = $this->getAuthors(true);
		$editorNames = array();
		foreach ($authors as $author) {
			if ($author->getIsVolumeEditor()) {
				$editorNames[] = __('submission.editorName', array('editorName' => $author->getFullName($preferred)));
			}
		}

		if (count($editorNames)) {
			// Spaces are stripped from the locale strings, so we have to add the
			// space in here.
			return join(__('common.commaListSeparator') . ' ', $editorNames);
		}

		return $this->getAuthorString($preferred);
	}
}


