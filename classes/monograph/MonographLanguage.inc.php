<?php

/**
 * @file classes/monograph/MonographLanguage.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographLanguage
 * @ingroup monograph
 * @see MonographLanguageEntryDAO
 *
 * @brief Basic class describing a monograph language
 */


import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class MonographLanguage extends ControlledVocabEntry {
	//
	// Get/set methods
	//

	/**
	 * Get the language
	 * @return string
	 */
	function getLanguage() {
		return $this->getData('monographLanguage');
	}

	/**
	 * Set the language text
	 * @param language string
	 * @param locale string
	 */
	function setLanguage($language, $locale) {
		$this->setData('monographLanguage', $language, $locale);
	}

	function getLocaleMetadataFieldNames() {
		return array('monographLanguage');
	}
}
?>