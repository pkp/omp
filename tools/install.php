<?php

/**
 * @file tools/install.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class installTool
 * @ingroup tools
 *
 * @brief CLI tool for installing OMP.
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.InstallTool');

class OMPInstallTool extends InstallTool {
	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function __construct($argv = array()) {
		parent::__construct($argv);
	}

	/**
	 * Read installation parameters from stdin.
	 * FIXME: May want to implement an abstract "CLIForm" class handling input/validation.
	 * FIXME: Use readline if available?
	 */
	function readParams() {
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_INSTALLER, LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);
		printf("%s\n", __('installer.appInstallation'));

		parent::readParams();

		$this->readParamBoolean('install', 'installer.installOMP');

		return $this->params['install'];
	}

}

$tool = new OMPInstallTool(isset($argv) ? $argv : array());
$tool->execute();


