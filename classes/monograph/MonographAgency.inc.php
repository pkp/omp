<?php

/**
 * @file MonographAgency.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographAgency
 * @ingroup monograph
 * @see MonographAgencyEntryDAO
 *
 * @brief Basic class describing a monograph agency
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class MonographAgency extends ControlledVocabEntry {
	//
	// Get/set methods
	//

	/**
	 * Get the agency
	 * @return string
	 */
	function getAgency() {
		return $this->getData('monograph_agency');
	}

	/**
	 * Set the agency text
	 * @param agency
	 */
	function setAgency($agency) {
		$this->setData('monograph_agency', $agency);
	}
}
?>
