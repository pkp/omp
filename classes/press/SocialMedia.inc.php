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
	 * Get whether or not this should be included on a monograph's catalog page.
	 * @return boolean
	 */
	function getIncludeInCatalog() {
		return $this->getData('includeInCatalog');
	}

	/**
	 * Set whether or not this should be included on a monograph's catalog page.
	 * @param $path string
	 */
	function setIncludeInCatalog($includeInCatalog) {
		return $this->setData('includeInCatalog', $includeInCatalog);
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

	/**
	 * Replace various variables in the code template with data
	 * relevant to the assigned monograph.
	 * @param PublishedMonograph $publishedMonograph
	 */
	function replaceCodeVars($publishedMonograph = null) {

		$application =& Application::getApplication();
		$request =& $application->getRequest();
		$router =& $request->getRouter();
		$press =& $request->getPress();

		$code = $this->getCode();

		$codeVariables = array(
				'pressUrl' => $router->url($request, null, 'index'),
				'pressName' => $press->getLocalizedName(),
			);

		if (isset($publishedMonograph)) {
			$codeVariables = array_merge($codeVariables, array(
				'bookCatalogUrl' => $router->url($request, null, 'catalog', 'book', $publishedMonograph->getId()),
				'bookTitle' => $publishedMonograph->getLocalizedTitle(),
			));
		}

		// Replace variables in message with values
		foreach ($codeVariables as $key => $value) {
			if (!is_object($value)) {
				$code = str_replace('{$' . $key . '}', $value, $code);
			}
		}

		$this->setCode($code);
	}
}

?>
