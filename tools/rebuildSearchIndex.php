<?php

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the monograph keyword search database.
 */

use APP\core\Application;
use PKP\cliTool\CommandLineTool;

require(dirname(__FILE__) . '/bootstrap.php');

class rebuildSearchIndex extends CommandLineTool
{
    /**
     * Print command usage information.
     */
    public function usage()
    {
        echo "Script to rebuild monograph search index\n"
            . "Usage: {$this->scriptName}\n";
    }

    /**
     * Rebuild the search index for all monographs in all presses.
     */
    public function execute()
    {
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->rebuildIndex(true);
    }
}

$tool = new rebuildSearchIndex($argv ?? []);
$tool->execute();
