<?php

/**
 * @defgroup plugins_metadata_mods_schema
 */

/**
 * @file plugins/metadata/mods/schema/ModsSchema.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ModsSchema
 * @ingroup plugins_metadata_mods_schema
 * @see PKPModsSchema
 *
 * @brief OMP-specific implementation of the ModsSchema.
 */


import('lib.pkp.plugins.metadata.mods.schema.PKPModsSchema');

class ModsSchema extends PKPModsSchema {
	/**
	 * Constructor
	 */
	function ModsSchema() {
		// Configure the MODS schema.
		parent::PKPModsSchema(ASSOC_TYPE_MONOGRAPH);
	}
}
?>