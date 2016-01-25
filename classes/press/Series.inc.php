<?php

/**
 * @file classes/press/Series.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
	function Series() {
		parent::PKPSection();
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
	 * Get the series full title (with title and subtitle).
	 * @return string
	 */
	function getLocalizedFullTitle() {
		$fullTitle = null;
		if ($prefix = $this->getLocalizedPrefix()) {
			$fullTitle = $prefix . ' ';
		}

		$fullTitle .= $this->getLocalizedTitle();

		if ($subtitle = $this->getLocalizedSubtitle()) {
			$fullTitle = String::concatTitleFields(array($fullTitle, $subtitle));
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
	 * Returns a string with the full name of all series
	 * editors, separated by a comma.
	 * @return string
	 */
	function getEditorsString() {
		$seriesEditorsDao = DAORegistry::getDAO('SeriesEditorsDAO');
		$editors = $seriesEditorsDao->getBySeriesId($this->getId(), $this->getPressId());

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

?>
