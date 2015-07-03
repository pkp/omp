<?php

/**
 * @file plugins/metadata/mods34/schema/Mods34Schema.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34Schema
 * @ingroup plugins_metadata_mods34_schema
 * @see PKPMods34Schema
 *
 * @brief OMP-specific implementation of the Mods34Schema.
 */


import('lib.pkp.plugins.metadata.mods34.schema.PKPMods34Schema');

class Mods34Schema extends PKPMods34Schema {
	/**
	 * Constructor
	 */
	function Mods34Schema() {
		// Configure the MODS schema.
		parent::PKPMods34Schema(ASSOC_TYPE_MONOGRAPH);
	}
}
?>
