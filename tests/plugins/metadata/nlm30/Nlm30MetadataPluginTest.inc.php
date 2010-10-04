<?php

/**
 * @defgroup tests_plugins_metadata_nlm30
 */

/**
 * @file tests/plugins/metadata/nlm30/Nlm30MetadataPluginTest.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30MetadataPluginTest
 * @ingroup tests_plugins_metadata_nlm30
 * @see Nlm30MetadataPlugin
 *
 * @brief Test class for Nlm30MetadataPlugin.
 */


import('lib.pkp.tests.plugins.metadata.MetadataPluginTestCase');

class Nlm30MetadataPluginTest extends MetadataPluginTestCase {
	/**
	 * @covers Nlm30MetadataPlugin
	 * @covers PKPNlm30MetadataPlugin
	 */
	public function testNlm30MetadataPlugin() {
		$this->executeMetadataPluginTest(
			'nlm30',
			'Nlm30MetadataPlugin',
			array('citation=>nlm30', 'nlm30=>citation', 'plaintext=>nlm30-element-citation',
					'nlm30-element-citation=>nlm30-element-citation', 'nlm30-element-citation=>plaintext',
					'submission=>nlm30-article', 'submission=>nlm23-article', 'nlm30-article=>nlm23-article',
					'submission=>reference-list'),
			array('nlm30-publication-types')
		);
	}
}
?>