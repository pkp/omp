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


class AcquisitionsArrangement extends DataObject {

	/**
	 * Constructor.
	 */
	function AcquisitionsArrangement() {
		parent::DataObject();
	}

	/**
	 * Get localized title of press section.
	 * @return string
	 */
	function getAcquisitionsArrangementTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get localized abbreviation of press section.
	 * @return string
	 */
	function getAcquisitionsArrangementAbbrev() {
		return $this->getLocalizedData('abbrev');
	}

	//
	// Get/set methods
	//
	function setArrangementType($value) {
		$this->setData('type', $value);
	}
	function getArrangementType() {
		return $this->getData('type');
	}

	/**
	 * Get ID of section.
	 * @return int
	 */
	function getAcquisitionsArrangementId() {
		return $this->getData('arrangementId');
	}

	/**
	 * Set ID of section.
	 * @param $arrangementId int
	 */
	function setAcquisitionsArrangementId($arrangementId) {
		return $this->setData('arrangementId', $arrangementId);
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
	 * Get title of section.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of section.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get section title abbreviation.
	 * @param $locale string
	 * @return string
	 */
	function getAbbrev($locale) {
		return $this->getData('abbrev', $locale);
	}

	/**
	 * Set section title abbreviation.
	 * @param $abbrev string
	 * @param $locale string
	 */
	function setAbbrev($abbrev, $locale) {
		return $this->setData('abbrev', $abbrev, $locale);
	}

	/**
	 * Get sequence of section.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence of section.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Get open archive setting of section.
	 * @return boolean
	 */
	function getMetaIndexed() {
		return $this->getData('metaIndexed');
	}

	/**
	 * Set open archive setting of section.
	 * @param $metaIndexed boolean
	 */
	function setMetaIndexed($metaIndexed) {
		return $this->setData('metaIndexed', $metaIndexed);
	}

	/**
	 * Return boolean indicating whether or not submissions are restricted to [section]Editors.
	 * @return boolean
	 */
	function getEditorRestricted() {
		return $this->getData('editorRestricted');
	}

	/**
	 * Set whether or not submissions are restricted to [section]Editors.
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
	 * Get localized section policy.
	 * @return string
	 */
	function getAcquisitionsArrangementPolicy() {
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
}

?>
