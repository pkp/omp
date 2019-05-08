<?php

/**
 * @file plugins/metadata/dc11/schema/Dc11Schema.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dc11Schema
 * @ingroup plugins_metadata_dc11_schema
 * @see PKPDc11Schema
 *
 * @brief OMP-specific implementation of the Dc11Schema.
 */


import('lib.pkp.plugins.metadata.dc11.schema.PKPDc11Schema');

class Dc11Schema extends PKPDc11Schema {
	/**
	 * Constructor
	 */
	function __construct() {
		// Configure the MODS schema.
		parent::__construct(ASSOC_TYPE_PUBLICATION_FORMAT);
	}
}

