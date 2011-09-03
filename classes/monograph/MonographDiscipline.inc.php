<?php

/**
 * @file MonographDiscipline.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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
		return $this->getData('monograph_discipline');
	}

	/**
	 * Set the discipline text
	 * @param discipline
	 */
	function setDiscipline($discipline) {
		$this->setData('monograph_discipline', $discipline);
	}
}
?>
