<?php

/**
 * @file plugins/importexport/users/UserImportExportPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UserImportExportPlugin
 * @ingroup plugins_importexport_user
 *
 * @brief User XML import/export plugin
 */

import('lib.pkp.plugins.importexport.users.PKPUserImportExportPlugin');

class UserImportExportPlugin extends PKPUserImportExportPlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		return parent::register($category, $path, $mainContextId);
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI
	 */
	function executeCLI($scriptName, &$args) {
		fatalError('Not implemented.');
	}

	/**
	 * @copydoc ImportExportPlugin::usage
	 */
	function usage($scriptName) {
		fatalError('Not implemented.');
	}
	/**
	 * Define the appropriate import filter given the imported XML file path
	 *
	 * @param string $xmlFile
	 *
	 * @return array Containing the filter and the xmlString of the imported file
	 */
	function getImportFilter($xmlFile) {
		fatalError('Not implemented.');
	}

	/**
	 * Define the appropriate export filter given the export operation
	 *
	 * @param string $exportType
	 *
	 * @return string
	 */
	function getExportFilter($exportType) {
		fatalError('Not implemented.');
	}

	/**
	 * Get the application specific deployment object
	 *
	 * @param Context $context
	 * @param User $user
	 *
	 * @return PKPImportExportDeployment
	 */
	function getAppSpecificDeployment($context, $user) {
		fatalError('Not implemented.');
	}
}


