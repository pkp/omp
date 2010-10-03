<?php

/**
 * @defgroup plugins_metadata_mods34
 */

/**
 * @file plugins/metadata/mods34/Mods34MetadataPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34MetadataPlugin
 * @ingroup plugins_metadata_mods34
 *
 * @brief MODS metadata plugin
 */


import('lib.pkp.plugins.metadata.mods34.PKPMods34MetadataPlugin');

class Mods34MetadataPlugin extends PKPMods34MetadataPlugin {
	//
	// Override protected template methods from MetadataPlugin.
	//
	/**
	 * @see MetadataPlugin::getMetadataAdapterNames()
	 */
	function getMetadataAdapterNames() {
		return array('plugins.metadata.mods34.filter.Mods34SchemaMonographAdapter');
	}
}

?>
