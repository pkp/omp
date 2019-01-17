<?php

/**
 * @file tools/fixFilenames.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class fixFilenames
 * @ingroup tools
 *
 * @brief CLI tool to fix pre-1.0 filenames that may have been incorrectly generated. See bug #8461.
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.CliTool');

class fixFilenames extends CommandLineTool {
	/** @var $dryrun boolean True iff the operation should be a "dry run" (no changes made) only. */
	var $dryrun;

	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function __construct($argv = array()) {
		parent::__construct($argv);

		if (($arg = array_pop($this->argv)) == '--dry-run') {
			$this->dryrun = true;
		} elseif ($arg == '') {
			$this->dryrun = false;
		} else {
			$this->usage();
			exit();
		}
	}

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Command-line tool for fixing potential incorrectly named files in OMP 1.0\n"
			. "Usage:\n"
			. "\t{$this->scriptName} [--dry-run]\n"
			. "\t\tThe --dry-run option can be used to test without making changes.\n";
	}

	/**
	 * Execute upgrade task
	 */
	function execute() {
		import('classes.install.Upgrade');
		Upgrade::fixFilenames($this->dryrun);
	}
}

$tool = new fixFilenames(isset($argv) ? $argv : array());
$tool->execute();


