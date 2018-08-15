<?php

/**
 * @file classes/press/Series.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Series
 * @ingroup press
 * @see SeriesDAO
 *
 * @brief Describes basic series properties.
 */

import('lib.pkp.classes.context.PKPSection');

class Series extends PKPSection {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get ID of press.
	 * @return int
	 */
	function getPressId() {
		return $this->getContextId();
	}

	/**
	 * Set ID of press.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setContextId($pressId);
	}

	/**
	 * Get localized title of section.
	 * @param $includePrefix bool
	 * @return string
	 */
	function getLocalizedTitle($includePrefix = true) {
		$title = $this->getLocalizedData('title');
		if ($includePrefix) {
			$title = $this->getLocalizedPrefix() . ' ' . $title;
		}
		return $title;
	}

	/**
	 * Get title of section.
	 * @param $locale
	 * @param $includePrefix bool
	 * @return string
	 */
	function getTitle($locale, $includePrefix = true) {
		$title = $this->getData('title', $locale);
		if ($includePrefix) {
			if (is_array($title)) {
				foreach($title as $locale => $currentTitle) {
					$title[$locale] = $this->getPrefix($locale) . ' ' . $currentTitle;
				}
			} else {
				$title = $this->getPrefix($locale) . ' ' . $title;
			}
		}
		return $title;
	}

	/**
	 * Get the series full title (with title and subtitle).
	 * @return string
	 */
	function getLocalizedFullTitle() {
		$fullTitle = $this->getLocalizedTitle();

		if ($subtitle = $this->getLocalizedSubtitle()) {
			$fullTitle = PKPString::concatTitleFields(array($fullTitle, $subtitle));
		}

		return $fullTitle;
	}

	/**
	 * Get localized prefix for the series.
	 * @return string
	 */
	function getLocalizedPrefix() {
		return $this->getLocalizedData('prefix');
	}

	/**
	 * Get prefix of series.
	 * @param $locale string
	 * @return string
	 */
	function getPrefix($locale) {
		return $this->getData('prefix', $locale);
	}

	/**
	 * Set prefix of series.
	 * @param $prefix string
	 * @param $locale string
	 */
	function setPrefix($prefix, $locale) {
		return $this->setData('prefix', $prefix, $locale);
	}

	/**
	 * Get the localized version of the subtitle
	 * @return string
	 */
	function getLocalizedSubtitle() {
		return $this->getLocalizedData('subtitle');
	}

	/**
	 * Get the subtitle for a given locale
	 * @param string $locale
	 * @return string
	 */
	function getSubtitle($locale) {
		return $this->getData('subtitle', $locale);
	}

	/**
	 * Set the subtitle for a locale
	 * @param string $subtitle
	 * @param string $locale
	 */
	function setSubtitle($subtitle, $locale) {
		return $this->setData('subtitle', $subtitle, $locale);
	}

	/**
	 * Get path to series (in URL).
	 * @return string
	 */
	function getPath() {
		return $this->getData('path');
	}

	/**
	 * Set path to series (in URL).
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get series description.
	 * @return string
	 */
	function getLocalizedDescription() {
		return $this->getLocalizedData('description');
	}

	/**
	 * Get series description.
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getData('description', $locale);
	}

	/**
	 * Set series description.
	 * @param string
	 */
	function setDescription($description, $locale) {
		$this->setData('description', $description, $locale);
	}

	/**
	 * Get the featured flag.
	 * @return boolean
	 */
	function getFeatured() {
		return $this->getData('featured');
	}

	/**
	 * Set the featured flag.
	 * @param $featured boolean
	 */
	function setFeatured($featured) {
		$this->setData('featured', $featured);
	}

	/**
	 * Get the image.
	 * @return array
	 */
	function getImage() {
		return $this->getData('image');
	}

	/**
	 * Set the image.
	 * @param $image array
	 */
	function setImage($image) {
		return $this->setData('image', $image);
	}

	/**
	 * Get online ISSN.
	 * @return string
	 */
	function getOnlineISSN() {
		return $this->getData('onlineIssn');
	}

	/**
	 * Set online ISSN.
	 * @param $onlineIssn string
	 */
	function setOnlineISSN($onlineIssn) {
		return $this->setData('onlineIssn', $onlineIssn);
	}

	/**
	 * Get print ISSN.
	 * @return string
	 */
	function getPrintISSN() {
		return $this->getData('printIssn');
	}

	/**
	 * Set print ISSN.
	 * @param $printIssn string
	 */
	function setPrintISSN($printIssn) {
		return $this->setData('printIssn', $printIssn);
	}

	/**
	 * Get the option how the books in this series should be sorted,
	 * in the form: concat(sortBy, sortDir).
	 * @return string
	 */
	function getSortOption() {
		return $this->getData('sortOption');
	}

	/**
	 * Set the option how the books in this series should be sorted,
	 * in the form: concat(sortBy, sortDir).
	 * @param $sortOption string
	 */
	function setSortOption($sortOption) {
		return $this->setData('sortOption', $sortOption);
	}

	/**
	 * Returns a string with the full name of all series
	 * editors, separated by a comma.
	 * @return string
	 */
	function getEditorsString() {
		$subEditorsDao = DAORegistry::getDAO('SubEditorsDAO');
		$editors = $subEditorsDao->getBySectionId($this->getId(), $this->getPressId());

		$separator = ', ';
		$str = '';

		foreach ($editors as $editor) {
			if (!empty($str)) {
				$str .= $separator;
			}

			$str .= $editor->getFullName();
			$editor = null;
		}

		return $str;
	}
}


