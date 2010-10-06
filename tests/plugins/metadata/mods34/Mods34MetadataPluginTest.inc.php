<?php

/**
 * @defgroup tests_plugins_metadata_mods34
 */

/**
 * @file tests/plugins/metadata/mods34/Mods34MetadataPluginTest.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34MetadataPluginTest
 * @ingroup tests_plugins_metadata_mods34
 * @see Mods34MetadataPlugin
 *
 * @brief Test class for Mods34MetadataPlugin.
 */


import('lib.pkp.tests.plugins.metadata.MetadataPluginTestCase');

class Mods34MetadataPluginTest extends MetadataPluginTestCase {
	/**
	 * @covers Mods34MetadataPlugin
	 * @covers PKPMods34MetadataPlugin
	 */
	public function testMods34MetadataPlugin() {
		$this->executeMetadataPluginTest(
			'mods34',
			'Mods34MetadataPlugin',
			array('monograph=>mods34', 'mods34=>monograph', 'mods34=>mods34-xml'),
			array('mods34-name-types', 'mods34-name-role-roleTerms-marcrelator',
				'mods34-typeOfResource', 'mods34-genre-marcgt', 'mods34-physicalDescription-form-marcform')
		);
	}
}
?>