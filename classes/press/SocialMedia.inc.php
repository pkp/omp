<?php

/**
 * @file classes/press/SocialMedia.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SocialMedia
 * @ingroup press
 * @see SocialMediaDAO
 *
 * @brief Describes basic SocialMedia properties.
 */

class SocialMedia extends DataObject {
	/**
	 * Constructor.
	 */
	function SocialMedia() {
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
	 * Get media block code.
	 * @return string
	 */
	function getCode() {
		return $this->getData('code');
	}

	/**
	 * Set media block code.
	 * @param $path string
	 */
	function setCode($code) {
		return $this->setData('code', $code);
	}

	/**
	 * Get localized platform name.
	 * @return string
	 */
	function getLocalizedPlatform() {
		return $this->getLocalizedData('platform');
	}

	/**
	 * Get media platform.
	 * @param $locale string
	 * @return string
	 */
	function getPlatform($locale) {
		return $this->getData('platform', $locale);
	}

	/**
	 * Set media platform.
	 * @param $title string
	 * @param $locale string
	 */
	function setPlatform($platform, $locale) {
		return $this->setData('platform', $platform, $locale);
	}
}

?>
