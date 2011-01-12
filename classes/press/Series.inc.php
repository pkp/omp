<?php

/**
 * @file classes/press/Series.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Series
 * @ingroup press
 * @see SeriesDAO
 *
 * @brief Describes basic series properties.
 */


define('SERIES_TYPE_SERIES',	1);
define('SERIES_TYPE_CATEGORY',	2);

class Series extends DataObject {

	/**
	 * Constructor.
	 */
	function Series() {
		parent::DataObject();
	}

	/**
	 * Get ID of press.
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set ID of press.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get localized title of the series.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of series.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of series.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get series division.
	 * @return int
	 */
	function getDivisionId() {
		return $this->getData('division');
	}

	/**
	 * Set series division.
	 * @return int
	 */
	function setDivisionId($value) {
		$this->setData('division', $value);
	}

	/**
	 * Get series affiliation.
	 * @return string
	 */
	function getLocalizedAffiliation() {
		return $this->getLocalizedData('affiliation');
	}

	/**
	 * Get series affiliation.
	 * @return string
	 */
	function getAffiliation($locale) {
		return $this->getData('affiliation', $locale);
	}

	/**
	 * Set series affiliation.
	 * @param string
	 */
	function setAffiliation($affiliation, $locale) {
		$this->setData('affiliation', $affiliation, $locale);
	}
}

?>
