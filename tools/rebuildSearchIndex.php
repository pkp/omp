<?php

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 *
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the monograph keyword search database.
 */

require dirname(__FILE__) . '/bootstrap.php';

use APP\core\Application;
use APP\facades\Repo;
use Illuminate\Support\Collection;
use PKP\cliTool\CommandLineTool;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;

class rebuildSearchIndex extends CommandLineTool
{
    /**
     * Print command usage information.
     */
    public function usage(): void
    {
        echo "Script to rebuild monograph search index\n"
            . "Usage: {$this->scriptName} [options] [press_path]\n\n"
            . "options: The standard index implementation does\n"
            . "         not support any options. For other\n"
            . "         implementations please see the corresponding\n"
            . "         plugin documentation (e.g. 'plugins/generic/\n"
            . "         lucene/README').\n";
    }

    /**
     * Rebuild the search index for all monographs in all presses.
     */
    public function execute(): void
    {
        // Check whether we have (optional) switches.
        $switches = [];
        while (count($this->argv) && substr($this->argv[0], 0, 1) === '-') {
            $switches[] = array_shift($this->argv);
        }

        // If we have another argument that this must be a press path.
        $press = null;
        if (count($this->argv)) {
            $pressPath = array_shift($this->argv);
            $pressDao = DAORegistry::getDAO('PressDAO'); /** @var \APP\press\PressDAO $pressDao */
            $press = $pressDao->getByPath($pressPath);
            if (!$press) {
                exit(__('search.cli.rebuildIndex.unknownPress', ['pressPath' => $pressPath]) . "\n");
            }
        }

        // Register a router hook so that we can construct
        // useful URLs to press content.
        Hook::add('Request::getBaseUrl', [$this, 'callbackBaseUrl']);

        $searchEngine = app(\Laravel\Scout\EngineManager::class)->engine();
        $searchEngine->flush(Config::getVar('search_index_name', 'submissions'));

        // Let the search implementation re-build the index.
        $submissions = Repo::submission()->getCollector()
            ->filterByContextIds([$journal?->getId() ?? Application::SITE_CONTEXT_ID_ALL])
            ->getIds()
            ->chunk(100)
            ->each(function (Collection $submissionIds) use ($searchEngine) {
                $submissions = Repo::submission()->getCollector()
                    ->filterByContextIds([Application::SITE_CONTEXT_ID_ALL])
                    ->filterBySubmissionIds($submissionIds->all())
                    ->getMany();
                $searchEngine->update($submissions);
            });
    }

    /**
     * Callback to patch the base URL which will be required
     * when constructing galley/supp file download URLs.
     *
     * @see \App\core\Request::getBaseUrl()
     */
    public function callbackBaseUrl(string $hookName, array $params): bool
    {
        $baseUrl = & $params[0];
        $baseUrl = Config::getVar('general', 'base_url');
        return Hook::ABORT;
    }
}

$tool = new rebuildSearchIndex($argv ?? []);
$tool->execute();
