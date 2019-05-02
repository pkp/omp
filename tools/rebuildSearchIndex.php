<?php

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the monograph keyword search database.
 */

require(dirname(__FILE__) . '/bootstrap.inc.php');

class rebuildSearchIndex extends CommandLineTool {

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Script to rebuild monograph search index\n"
			. "Usage: {$this->scriptName}\n";
	}

	/**
	 * Rebuild the search index for all monographs in all presses.
	 */
	function execute() {
		$monographSearchIndex = Application::getSubmissionSearchIndex();
		$monographSearchIndex->rebuildIndex(true);
	}

}

$tool = new rebuildSearchIndex(isset($argv) ? $argv : array());
$tool->execute();

