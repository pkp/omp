<?php

/**
 * @defgroup press Press
 */

/**
 * @file classes/press/Press.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Press
 * @ingroup press
 * @see PressDAO
 *
 * @brief Basic class describing a press.
 */

import('lib.pkp.classes.context.Context');

class Press extends Context {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get "localized" press page title (if applicable).
	 * @return string|null
	 * @deprecated 3.3.0, use getLocalizedData() instead
	 */
	function getLocalizedPageHeaderTitle() {
		$titleArray = $this->getData('name');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($titleArray[$locale])) return $titleArray[$locale];
		}
		return null;
	}

	/**
	 * @deprecated Since OMP 3.2.1, use getLocalizedPageHeaderTitle instead.
	 * @return string
	 */
	function getPageHeaderTitle() {
		return $this->getLocalizedPageHeaderTitle();
	}

	/**
	 * Get "localized" press page logo (if applicable).
	 * @return array|null
	 * @deprecated 3.3.0, use getLocalizedData() instead
	 */
	function getLocalizedPageHeaderLogo() {
		$logoArray = $this->getData('pageHeaderLogoImage');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($logoArray[$locale])) return $logoArray[$locale];
		}
		return null;
	}

	/**
	 * @deprecated Since OMP 3.2.1, use getLocalizedPageHeaderLogo instead.
	 * @return array|null
	 */
	function getPageHeaderLogo() {
		return $this->getLocalizedPageHeaderLogo();
	}

	/**
	 * Returns true if this press contains the fields required for creating valid
	 * ONIX export metadata.
	 * @return boolean
	 */
	function hasRequiredOnixHeaderFields() {
		if ($this->getData('codeType') != '' && $this->getData('codeValue') != '') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the association type for this context.
	 * @return int
	 */
	public function getAssocType() {
		return ASSOC_TYPE_PRESS;
	}

	/**
	 * Get the DAO for this context object.
	 * @return DAO
	 */
	function getDAO() {
		return DAORegistry::getDAO('PressDAO');
	}
}
