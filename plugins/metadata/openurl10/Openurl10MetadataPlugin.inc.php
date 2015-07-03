<?php

/**
 * @file plugins/metadata/openurl10/Openurl10MetadataPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Openurl10MetadataPlugin
 * @ingroup plugins_metadata_openurl10
 *
 * @brief OpenURL 1.0 metadata plugin
 */


import('lib.pkp.plugins.metadata.openurl10.PKPOpenurl10MetadataPlugin');

class Openurl10MetadataPlugin extends PKPOpenurl10MetadataPlugin {
	/**
	 * Constructor
	 */
	function Openurl10MetadataPlugin() {
		parent::PKPOpenurl10MetadataPlugin();
	}
}

?>
