<?php

/**
 * @file classes/monograph/MonographGalley.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographGalley
 * @ingroup monograph
 * @see MonographGalleyDAO
 *
 * @brief A galley is a final presentation version of the full-text of a monograph.
 */



import('classes.monograph.MonographFile');

class MonographGalley extends MonographFile {

	/**
	 * Constructor.
	 */
	function MonographGalley() {
		parent::DataObject();
	}

	/**
	 * Check if galley is an HTML galley.
	 * @return boolean
	 */
	function isHTMLGalley() {
		return false;
	}

	/**
	 * Check if galley is a PDF galley.
	 * @return boolean
	 */
	function isPdfGalley() {
		switch ($this->getFileType()) {
			case 'application/pdf':
			case 'application/x-pdf':
			case 'text/pdf':
			case 'text/x-pdf':
				return true;
			default: return false;
		}
	}

	/**
	 * Check if the specified file is a dependent file.
	 * @param $fileId int
	 * @return boolean
	 */
	function isDependentFile($fileId) {
		return false;
	}

	//
	// Get/set methods
	//

	/**
	 * Get views count.
	 * @return int
	 */
	function getViews() {
		return $this->getData('views');
	}

	/**
	 * Set views count.
	 * NOTE that the views count is NOT stored by the DAO update or insert functions.
	 * @param $views int
	 */
	function setViews($views) {
		return $this->setData('views', $views);
	}

	/**
	 * Get the localized value of the galley label.
	 * @return $string
	 */
	function getGalleyLabel() {
		$label = $this->getLabel();
		if ($this->getLocale() != Locale::getLocale()) {
			$locales = Locale::getAllLocales();
			$label .= ' (' . $locales[$this->getLocale()] . ')';
		}
		return $label;
	}

	/**
	 * Get production assignment Id.
	 * @return string
	 */
	function getAssignmentId() {
		return $this->getData('assignmentId');
	}

	/**
	 * Set production assignment Id.
	 * @param $assignmentId int
	 */
	function setAssignmentId($assignmentId) {
		return $this->setData('assignmentId', $assignmentId);
	}

	/**
	 * Get label/title.
	 * @return string
	 */
	function getLabel() {
		return $this->getData('label');
	}

	/**
	 * Set label/title.
	 * @param $label string
	 */
	function setLabel($label) {
		return $this->setData('label', $label);
	}

	/**
	 * Get locale.
	 * @return string
	 */
	function getLocale() {
		return $this->getData('locale');
	}

	/**
	 * Set locale.
	 * @param $locale string
	 */
	function setLocale($locale) {
		return $this->setData('locale', $locale);
	}

	/**
	 * Get sequence order of galley file.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence order of galley file.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Get public galley id
	 * @return string
	 */
	function getPublicGalleyId() {
		// Ensure that blanks are treated as nulls.
		$returner = $this->getData('publicGalleyId');
		if ($returner === '') return null;
		return $returner;
	}

	/**
	 * Set public galley id
	 * @param $publicGalleyId string
	 */
	function setPublicGalleyId($publicGalleyId) {
		return $this->setData('publicGalleyId', $publicGalleyId);
	}

	/**
	 * Return the "best" monograph ID -- If a public monograph ID is set,
	 * use it; otherwise use the internal monograph Id. (Checks the press
	 * settings to ensure that the public ID feature is enabled.)
	 * @param $press Object the Press this galley is in
	 * @return string
	 */
	function getBestGalleyId(&$press) {
		if ($press->getSetting('enablePublicGalleyId')) {
			$publicGalleyId = $this->getPublicGalleyId();
			if (!empty($publicGalleyId)) return $publicGalleyId;
		}
		return $this->getGalleyId();
	}
}

?>
