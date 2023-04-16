<?php

/**
 * @file pages/stats/StatsHandler.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsHandler
 *
 * @ingroup pages_stats
 *
 * @brief Handle requests for statistics pages.
 */

namespace APP\pages\stats;

use APP\core\Application;
use APP\facades\Repo;
use APP\section\Section;
use PKP\pages\stats\PKPStatsHandler;
use PKP\plugins\Hook;

class StatsHandler extends PKPStatsHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        Hook::add('TemplateManager::display', [$this, 'addSectionFilters']);
    }

    /**
     * Add OMP-specific configuration options to the stats component data
     *
     * Fired when the `TemplateManager::display` hook is called.
     *
     * @param array $args [$templateMgr, $template, $sendContentType, $charset, $output]
     */
    public function addSectionFilters($hookName, $args)
    {
        $templateMgr = $args[0];
        $template = $args[1];

        if (!in_array($template, ['stats/publications.tpl', 'stats/editorial.tpl'])) {
            return;
        }

        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return;
        }

        $seriesFilters = Repo::section()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany()
            ->map(fn (Section $series) => [
                'param' => 'seriesIds',
                'value' => $series->getId(),
                'title' => $series->getLocalizedTitle(),
            ])
            ->toArray();

        if (empty($seriesFilters)) {
            return;
        }

        $filters = $templateMgr->getState('filters');
        if (is_null($filters)) {
            $filters = [];
        }

        $filters[] = [
            'heading' => __('series.series'),
            'filters' => $seriesFilters,
        ];
        $templateMgr->setState([
            'filters' => $filters
        ]);
    }
}
