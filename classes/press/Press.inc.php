<?php

/**
 * @defgroup press
 */

/**
 * @file classes/press/Press.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Press
 * @ingroup press
 * @see PressDAO
 *
 * @brief Basic class describing a press.
 */

import('lib.pkp.classes.core.Context');

class Press extends Context {
	/**
	 * Constructor
	 */
	function Press() {
		parent::Context();
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
		$setting =& $pressSettingsDao->getSetting($this->getId(), $name, $locale);
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
	 * Get "localized" press page title (if applicable).
	 * param $home boolean get homepage title
	 * @return string
	 */
	function getPressPageHeaderTitle() {
		$typeArray = $this->getSetting('pageHeaderTitleType');
		$imageArray = $this->getSetting('pageHeaderTitleImage');
		$titleArray = $this->getSetting('pageHeaderTitle');

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
	 * @return string
	 */
	function getPressPageHeaderLogo() {
		$logoArray = $this->getSetting('pageHeaderLogoImage');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($logoArray[$locale])) return $logoArray[$locale];
		}
		return null;
	}

	/**
	 * Returns true if this press contains the fields required for creating valid
	 * ONIX export metadata.
	 * @return boolean
	 */
	function hasRequiredOnixHeaderFields() {
		if ($this->getSetting('codeType') != '' && $this->getSetting('codeValue') != '') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the association type for this context.
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_PRESS;
	}
}

?>
