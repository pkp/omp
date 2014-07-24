<?php

/**
 * @file tools/importCSVData.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class importCSVData
 * @ingroup tools
 *
 * @brief CLI tool to import CSV (comma separated value) data, instead of using the Native import XSD.
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.CliTool');

class importCSVData extends CommandLineTool {
	/** @var $dryrun boolean True iff the operation should be a "dry run" (no changes made) only. */
	var $dryrun;

	/** @var $filename String the file containing the CSV data to import. */
	var $filename;

	/** @var $username String the username to assign the submissions to. */
	var $username;
	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function importCSVData($argv = array()) {
		parent::CommandLineTool($argv);

		$options = getopt("u:f:", array("dry-run"));

		$this->dryrun = array_key_exists('dry-run', $options) ? true : false;

		$this->filename = array_key_exists('f', $options) ? $options['f'] : null;
		$this->username = array_key_exists('u', $options) ? $options['u'] : null;

		if (!$this->filename || !$this->username) {
			$this->usage();
			exit();
		}

		if (!file_exists($this->filename)) {
			echo "The file {$this->filename} does not exist.  Exiting! \n";
			exit();
		}
	}

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Command-line tool for importing CSV data into OMP\n"
			. "Usage:\n"
			. "\t{$this->scriptName} [--dry-run] "
			. "-f fileName.csv "
			. "-u username\n"
			. "\t\tThe --dry-run option can be used to test without making changes.\n"
			. "\t\tSpecify the username you wish to assign the submissions to.\n";
	}

	/**
	 * Execute upgrade task
	 */
	function execute() {
		import('classes.install.Upgrade');
		Upgrade::importCSVData($this->dryrun, $this->filename, $this->username);
	}
}

$tool = new importCSVData(isset($argv) ? $argv : array());
$tool->execute();

?>
