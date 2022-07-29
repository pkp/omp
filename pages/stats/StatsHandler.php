<?php

/**
 * @file pages/stats/StatsHandler.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsHandler
 * @ingroup pages_stats
 *
 * @brief Handle requests for statistics pages.
 */

import('lib.pkp.pages.stats.PKPStatsHandler');

use APP\template\TemplateManager;

class StatsHandler extends PKPStatsHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        HookRegistry::register('TemplateManager::display', [$this, 'addSectionFilters']);
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

        $seriesFilters = [];
        $result = \DAORegistry::getDAO('SeriesDAO')->getByContextId($context->getId());
        while ($series = $result->next()) {
            $seriesFilters[] = [
                'param' => 'seriesIds',
                'value' => $series->getId(),
                'title' => $series->getLocalizedTitle(),
            ];
        }

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
