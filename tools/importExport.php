<?php

/**
 * @file tools/importExport.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class importExport
 * @ingroup tools
 *
 * @brief CLI tool to perform import/export tasks
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

class importExport extends CommandLineTool
{
    public $command;
    public $plugin;
    public $parameters;

    /**
     * Constructor.
     *
     * @param array $argv command-line arguments (see usage)
     */
    public function __construct($argv = [])
    {
        parent::__construct($argv);
        $this->command = array_shift($this->argv);
        $this->parameters = $this->argv;
    }

    /**
     * Print command usage information.
     */
    public function usage()
    {
        echo "Command-line tool for import/export tasks\n"
            . "Usage:\n"
            . "\t{$this->scriptName} list: List available plugins\n"
            . "\t{$this->scriptName} [pluginName] usage: Display usage information for a plugin\n"
            . "\t{$this->scriptName} [pluginName] [params...]: Invoke a plugin\n";
    }

    /**
     * Parse and execute the import/export task.
     */
    public function execute()
    {
        $plugins = PluginRegistry::loadCategory('importexport');
        if ($this->command === 'list') {
            echo "Available plugins:\n";
            if (empty($plugins)) {
                echo "\t(None)\n";
            } else {
                foreach ($plugins as $plugin) {
                    echo "\t" . $plugin->getName() . "\n";
                }
            }
            return;
        }
        if ($this->command == 'usage' || $this->command == 'help' || $this->command == '' || ($plugin = PluginRegistry::getPlugin('importexport', $this->command)) === null) {
            $this->usage();
            return;
        }

        return $plugin->executeCLI($this->scriptName, $this->parameters);
    }
}

$tool = new importExport($argv ?? []);
$tool->execute();
