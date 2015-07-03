<?php

/**
 * @defgroup subject BIC Subjects
 */

/**
 * @file classes/codelist/Subject.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Subject
 * @ingroup codelist
 * @see SubjectDAO
 *
 * @brief Basic class describing a BIC Subject.
 *
 */

import('classes.codelist.CodelistItem');

class Subject extends CodelistItem {

	/**
	 * @var int The numerical representation of these Subject Qualifiers in ONIX 3.0
	 */
	var $_onixSubjectSchemeIdentifier = 12;

	/**
	 * Constructor
	 */
	function Subject() {
		parent::CodelistItem();
	}

	/**
	 * Get the ONIX subject scheme identifier.
	 * @return String the numerical value representing this item in the ONIX 3.0 schema
	 */
	function getOnixSubjectSchemeIdentifier() {
		return $this->_onixSubjectSchemeIdentifier;
	}
}

?>
