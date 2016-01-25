<?php

/**
 * @file tools/upgrade.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class upgradeTool
 * @ingroup tools
 *
 * @brief CLI tool for upgrading OMP.
 *
 * Note: Some functions require fopen wrappers to be enabled.
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.UpgradeTool');

class OMPUpgradeTool extends UpgradeTool {
	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function OMPUpgradeTool($argv = array()) {
		parent::UpgradeTool($argv);
	}
}

$tool = new OMPUpgradeTool(isset($argv) ? $argv : array());
$tool->execute();

?>
