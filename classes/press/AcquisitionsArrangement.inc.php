<?php

/**
 * @file classes/press/AcquisitionsArrangement.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsArrangement
 * @ingroup press
 * @see AcquisitionsArrangementDAO
 *
 * @brief Describes basic acquisitions arrangement properties.
 */

// $Id$


define('ARRANGEMENT_TYPE_SERIES',	1);
define('ARRANGEMENT_TYPE_CATEGORY',	2);

class AcquisitionsArrangement extends DataObject {

	/**
	 * Constructor.
	 */
	function AcquisitionsArrangement() {
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
	 * Get ID of primary review form.
	 * @return int
	 */
	function getReviewFormId() {
		return $this->getData('reviewFormId');
	}

	/**
	 * Set ID of primary review form.
	 * @param $reviewFormId int
	 */
	function setReviewFormId($reviewFormId) {
		return $this->setData('reviewFormId', $reviewFormId);
	}

	/**
	 * Get localized title of the acquisitions arrangement.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of arrangement.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of arrangement.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get localized abbreviation of the acquisitions arrangement.
	 * @return string
	 */
	function getLocalizedAbbrev() {
		return $this->getLocalizedData('abbrev');
	}

	/**
	 * Get arrangement title abbreviation.
	 * @param $locale string
	 * @return string
	 */
	function getAbbrev($locale) {
		return $this->getData('abbrev', $locale);
	}

	/**
	 * Set arrangement title abbreviation.
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setAbbrev($abbrev, $locale) {
		return $this->setData('abbrev', $abbrev, $locale);
	}

	/**
	 * Get sequence of arrangement.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence of arrangement.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Get open archive setting of arrangement.
	 * @return boolean
	 */
	function getMetaIndexed() {
		return $this->getData('metaIndexed');
	}

	/**
	 * Set open archive setting of arrangement.
	 * @param $metaIndexed boolean
	 */
	function setMetaIndexed($metaIndexed) {
		return $this->setData('metaIndexed', $metaIndexed);
	}

	/**
	 * Return boolean indicating whether or not submissions are restricted to [Acquisitions]Editors.
	 * @return boolean
	 */
	function getEditorRestricted() {
		return $this->getData('editorRestricted');
	}

	/**
	 * Set whether or not submissions are restricted to [Acquisitions]Editors.
	 * @param $editorRestricted boolean
	 */
	function setEditorRestricted($editorRestricted) {
		return $this->setData('editorRestricted', $editorRestricted);
	}

	/**
	 * Return boolean indicating if title should be hidden in About.
	 * @return boolean
	 */
	function getHideAbout() {
		return $this->getData('hideAbout');
	}

	/**
	 * Set if title should be hidden in About.
	 * @param $hideAbout boolean
	 */
	function setHideAbout($hideAbout) {
		return $this->setData('hideAbout', $hideAbout);
	}

	/**
	 * Return boolean indicating if RT comments should be disabled.
	 * @return boolean
	 */
	function getDisableComments() {
		return $this->getData('disableComments');
	}

	/**
	 * Set if RT comments should be disabled.
	 * @param $disableComments boolean
	 */
	function setDisableComments($disableComments) {
		return $this->setData('disableComments', $disableComments);
	}

	/**
	 * Get localized arrangement policy.
	 * @return string
	 */
	function getLocalizedPolicy() {
		return $this->getLocalizedData('policy');
	}

	/**
	 * Get policy.
	 * @param $locale string
	 * @return string
	 */
	function getPolicy($locale) {
		return $this->getData('policy', $locale);
	}

	/**
	 * Set policy.
	 * @param $policy string
	 * @param $locale string
	 */
	function setPolicy($policy, $locale) {
		return $this->setData('policy', $policy, $locale);
	}

	/**
	 * Get arrangement type.
	 * @return int
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Set arrangement type.
	 * @return int
	 */
	function setType($value) {
		$this->setData('type', $value);
	}

}

?>
