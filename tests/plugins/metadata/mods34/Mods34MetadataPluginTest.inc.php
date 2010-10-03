<?php

/**
 * @defgroup tests_plugins_metadata_mods
 */

/**
 * @file tests/plugins/metadata/mods34/Mods34MetadataPluginTest.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34MetadataPluginTest
 * @ingroup tests_plugins_metadata_mods
 * @see Mods34MetadataPlugin
 *
 * @brief Test class for Mods34MetadataPlugin.
 */

import('lib.pkp.tests.PKPTestCase');
import('plugins.metadata.mods34.Mods34MetadataPlugin');

class Mods34MetadataPluginTest extends PKPTestCase {
	/**
	 * @covers Mods34MetadataPlugin
	 */
	public function testMods34MetadataPlugin() {
		// Mock request and router.
		import('lib.pkp.classes.core.PKPRouter');
		$mockRequest = $this->getMock('Request', array('getRouter', 'getUser'));
		$router = new PKPRouter();
		$mockRequest->expects($this->any())
		            ->method('getRouter')
		            ->will($this->returnValue($router));
		$mockRequest->expects($this->any())
		            ->method('getUser')
		            ->will($this->returnValue(null));
		Registry::set('request', $mockRequest);

		// Instantiate the installer.
		import('classes.install.Install');
		$installFile = './lib/pkp/tests/plugins/testPluginInstall.xml';
		$params = $this->getConnectionParams();
		$installer = new Install($params, $installFile, true);

		// Parse the plug-ins version.xml.
		import('lib.pkp.classes.site.VersionCheck');
		self::assertFileExists($versionFile = './plugins/metadata/mods34/version.xml');
		self::assertArrayHasKey('version', $versionInfo =& VersionCheck::parseVersionXML($versionFile));
		self::assertType('Version', $pluginVersion =& $versionInfo['version']);
		$installer->setCurrentVersion($pluginVersion);

		self::assertTrue($installer->execute());
	}

	/**
	 * Load database connection parameters into an array (needed for upgrade).
	 * @return array
	 */
	function getConnectionParams() {
		return array(
			'clientCharset' => Config::getVar('i18n', 'client_charset'),
			'connectionCharset' => Config::getVar('i18n', 'connection_charset'),
			'databaseCharset' => Config::getVar('i18n', 'database_charset'),
			'databaseDriver' => Config::getVar('database', 'driver'),
			'databaseHost' => Config::getVar('database', 'host'),
			'databaseUsername' => Config::getVar('database', 'username'),
			'databasePassword' => Config::getVar('database', 'password'),
			'databaseName' => Config::getVar('database', 'name')
		);
	}
}
?>