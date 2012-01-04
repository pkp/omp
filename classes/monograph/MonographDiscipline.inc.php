<?php

/**
 * @file classes/monograph/MonographDiscipline.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDiscipline
 * @ingroup monograph
 * @see MonographDisciplineEntryDAO
 *
 * @brief Basic class describing a monograph discipline
 */


import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class MonographDiscipline extends ControlledVocabEntry {
	//
	// Get/set methods
	//

	/**
	 * Get the discipline
	 * @return string
	 */
	function getDiscipline() {
		return $this->getData('monographDiscipline');
	}

	/**
	 * Set the discipline text
	 * @param discipline string
	 * @param locale string
	 */
	function setDiscipline($discipline, $locale) {
		$this->setData('monographDiscipline', $discipline, $locale);
	}

	function getLocaleMetadataFieldNames() {
		return array('monographDiscipline');
	}
}
?>
