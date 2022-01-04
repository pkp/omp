<?php

/**
 * @file tools/fixFilenames.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class fixFilenames
 * @ingroup tools
 *
 * @brief CLI tool to fix pre-1.0 filenames that may have been incorrectly generated. See bug #8461.
 */

use APP\install\Upgrade;
use PKP\cliTool\CliTool;

require(dirname(__FILE__) . '/bootstrap.inc.php');

class fixFilenames extends CommandLineTool
{
    /** @var boolean $dryrun True iff the operation should be a "dry run" (no changes made) only. */
    public $dryrun;

    /**
     * Constructor.
     *
     * @param $argv array command-line arguments
     */
    public function __construct($argv = [])
    {
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
    public function usage()
    {
        echo "Command-line tool for fixing potential incorrectly named files in OMP 1.0\n"
            . "Usage:\n"
            . "\t{$this->scriptName} [--dry-run]\n"
            . "\t\tThe --dry-run option can be used to test without making changes.\n";
    }

    /**
     * Execute upgrade task
     */
    public function execute()
    {
        Upgrade::fixFilenames($this->dryrun);
    }
}

$tool = new fixFilenames($argv ?? []);
$tool->execute();
