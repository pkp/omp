<?php

/**
 * @defgroup press
 */

/**
 * @file classes/press/Press.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Press
 * @ingroup press
 * @see PressDAO
 *
 * @brief Basic class describing a press.
 */

class Press extends DataObject {

	/**
	 * Get the ID of the press
	 * @return int
	 */
	function getId() {
		return $this->getData('pressId');
	}

	/**
	 * Set the ID of the press.
	 * @param $announcementId int
	 */
	function setId($pressId) {
		$this->setData('pressId', $pressId);
	}

	/**
	 * Get the name of the press
	 * @return string
	 */
	function getLocalizedName() {
		return $this->getLocalizedSetting('name');
	}

	/**
	 * Set the name of the press
	 * @param $pressName string
	 */
	function setPressName($pressName) {
		$this->setData('name', $pressName);
	}

	/**
	 * get the name of the press
	 */
	function getName($locale) {
		return $this->getSetting('name', $locale);
	}

	/**
	 * Get press description.
	 * @return string
	 */
	function getDescription() {
		return $this->getData('description');
	}

	/**
	 * Set announcement description.
	 * @param $description string
	 * @param $locale string
	 */
	function setDescription($description) {
		$this->setData('description', $description);
	}

	/**
	 * Get path to press (in URL).
	 * @return string
	 */
	function getPath() {
		return $this->getData('path');
	}

	/**
	 * Set path to press (in URL).
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get enabled flag of press
	 * @return int
	 */
	function getEnabled() {
		return $this->getData('enabled');
	}

	/**
	 * Set enabled flag of press
	 * @param $enabled int
	 */
	function setEnabled($enabled) {
		return $this->setData('enabled',$enabled);
	}

	/**
	 * Return the primary locale of this press.
	 * @return string
	 */
	function getPrimaryLocale() {
		return $this->getData('primaryLocale');
	}

	/**
	 * Set the primary locale of this press.
	 * @param $locale string
	 */
	function setPrimaryLocale($primaryLocale) {
		return $this->setData('primaryLocale', $primaryLocale);
	}
	/**
	 * Get sequence of press in site table of contents.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence of press in site table of contents.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Get the localized description of the press.
	 * @return string
	 */
	function getLocalizedDescription() {
		return $this->getLocalizedSetting('description');
	}

	/**
	 * Retrieve array of press settings.
	 * @return array
	 */
	function &getSettings() {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$settings =& $pressSettingsDao->getPressSettings($this->getData('pressId'));
		return $settings;
	}

	/**
	 * Retrieve a press setting value.
	 * @param $name string
	 * @param $locale string
	 * @return mixed
	 */
	function &getSetting($name, $locale = null) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$setting =& $pressSettingsDao->getSetting($this->getData('pressId'), $name, $locale);
		return $setting;
	}

	/**
	 * Update a press setting value.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string optional
	 * @param $isLocalized boolean optional
	 */
	function updateSetting($name, $value, $type = null, $isLocalized = false) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		return $pressSettingsDao->updateSetting($this->getId(), $name, $value, $type, $isLocalized);
	}

	function &getLocalizedSetting($name) {
		$returner = $this->getSetting($name, AppLocale::getLocale());
		if ($returner === null) {
			unset($returner);
			$returner = $this->getSetting($name, AppLocale::getPrimaryLocale());
		}
		return $returner;
	}
	/**
	 * Return associative array of all locales supported by forms on the site.
	 * These locales are used to provide a language toggle on the main site pages.
	 * @return array
	 */
	function &getSupportedFormLocaleNames() {
		$supportedLocales =& $this->getData('supportedFormLocales');

		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames =& AppLocale::getAllLocales();

			$locales = $this->getSetting('supportedFormLocales');
			if (!isset($locales) || !is_array($locales)) {
				$locales = array();
			}

			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
		}

		return $supportedLocales;
	}

	/**
	 * Get "localized" press page title (if applicable).
	 * param $home boolean get homepage title
	 * @return string
	 */
	function getPressPageHeaderTitle($home = false) {
		$prefix = $home ? 'home' : 'page';
		$typeArray = $this->getSetting($prefix . 'HeaderTitleType');
		$imageArray = $this->getSetting($prefix . 'HeaderTitleImage');
		$titleArray = $this->getSetting($prefix . 'HeaderTitle');

		$title = null;

		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($typeArray[$locale]) && $typeArray[$locale]) {
				if (isset($imageArray[$locale])) $title = $imageArray[$locale];
			}
			if (empty($title) && isset($titleArray[$locale])) $title = $titleArray[$locale];
			if (!empty($title)) return $title;
		}
		return null;
	}

	/**
	 * Get "localized" press page logo (if applicable).
	 * param $home boolean get homepage logo
	 * @return string
	 */
	function getPressPageHeaderLogo($home = false) {
		$prefix = $home ? 'home' : 'page';
		$logoArray = $this->getSetting($prefix . 'HeaderLogoImage');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($logoArray[$locale])) return $logoArray[$locale];
		}
		return null;
	}

	/**
	 * Return associative array of all locales supported by the site.
	 * These locales are used to provide a language toggle on the main site pages.
	 * @return array
	 */
	function &getSupportedLocaleNames() {
		$supportedLocales =& $this->getData('supportedLocales');

		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames =& AppLocale::getAllLocales();

			$locales = $this->getSetting('supportedLocales');
			if (!isset($locales) || !is_array($locales)) {
				$locales = array();
			}

			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
		}

		return $supportedLocales;
	}


}

?>