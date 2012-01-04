<?php

/**
 * @file classes/monograph/MonographSubject.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSubject
 * @ingroup monograph
 * @see MonographSubjectEntryDAO
 *
 * @brief Basic class describing a monograph subject
 */


import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class MonographSubject extends ControlledVocabEntry {
	//
	// Get/set methods
	//

	/**
	 * Get the subject
	 * @return string
	 */
	function getSubject() {
		return $this->getData('monographSubject');
	}

	/**
	 * Set the subject text
	 * @param subject string
	 * @param locale string
	 */
	function setSubject($subject, $locale) {
		$this->setData('monographSubject', $subject, $locale);
	}

	function getLocaleMetadataFieldNames() {
		return array('monographSubject');
	}
}
?>