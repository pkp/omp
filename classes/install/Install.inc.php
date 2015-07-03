<?php

/**
 * @file classes/install/Install.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Install
 * @ingroup install
 * @see Installer, InstallForm
 *
 * @brief Perform system installation.
 *
 * This script will:
 *  - Create the database (optionally), and install the database tables and initial data.
 *  - Update the config file with installation parameters.
 */


// Default installation data
define('INSTALLER_DEFAULT_CONTACT', 'common.omp');
define('INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH', 6);

import('lib.pkp.classes.install.PKPInstall');

class Install extends PKPInstall {

	/**
	 * Constructor.
	 * @see install.form.InstallForm for the expected parameters
	 * @param $params array installer parameters
	 * @param $descriptor string descriptor path
	 * @param $isPlugin boolean true iff a plugin is being installed
	 */
	function Install($params, $descriptor = 'install.xml', $isPlugin = false) {
		parent::PKPInstall($descriptor, $params, $isPlugin);
	}

	//
	// Installer actions
	//

	/**
	 * Get the names of the directories to create.
	 * @return array
	 */
	function getCreateDirectories() {
		$directories = parent::getCreateDirectories();
		$directories[] = 'presses';
		return $directories;
	}

	/**
	 * Create initial required data.
	 * @return boolean
	 */
	function createData() {

		$createData = parent::createData();


		return $createData;
	}
}

?>
