<?php

/**
 * @defgroup qualifier BIC Qualifiers
 */

/**
 * @file classes/codelist/Qualifier.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Qualifier
 * @ingroup codelist
 * @see QualifierDAO
 *
 * @brief Basic class describing a BIC Qualifier.
 *
 */

import('classes.codelist.CodelistItem');

class Qualifier extends CodelistItem {

	/**
	 * @var int The numerical representation of these Subject Qualifiers in ONIX 3.0
	 */
	var $_onixSubjectSchemeIdentifier = 17;

	/**
	 * Constructor
	 */
	function Qualifier() {
		parent::CodelistItem();
	}

	/**
	 * @return String the numerical value representing this item in the ONIX 3.0 schema
	 */
	function getOnixSubjectSchemeIdentifier() {
		return $this->_onixSubjectSchemeIdentifier;
	}
}

?>
