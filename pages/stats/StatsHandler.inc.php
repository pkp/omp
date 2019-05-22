<?php

/**
 * @file pages/stats/StatsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatsHandler
 * @ingroup pages_stats
 *
 * @brief Handle requests for statistics pages.
 */

import('lib.pkp.pages.stats.PKPStatsHandler');

class StatsHandler extends PKPStatsHandler {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		HookRegistry::register ('TemplateManager::display', array($this, 'addMonographStatsData'));
	}

	/**
	 * Add OMP-specific configuration options to the stats component data
	 *
	 * Fired when the `TemplateManager::display` hook is called.
	 *
	 * @param string $hookname
	 * @param array $args [$templateMgr, $template, $sendContentType, $charset, $output]
	 */
	public function addMonographStatsData($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		if ($template !== 'stats/publications.tpl') {
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

		$statsComponent = $templateMgr->getTemplateVars('statsComponent');
		$statsComponent->filters = [
			[
				'heading' => __('series.series'),
				'filters' => $seriesFilters,
			],
		];
	}
}
