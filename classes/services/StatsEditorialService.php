<?php
/**
 * @file classes/services/StatsEditorialService.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PKPStatsEditorialService
 * @ingroup services
 *
 * @brief Helper class that encapsulates business logic for getting
 *   editorial stats
 */

namespace APP\services;

use APP\decision\Decision;

class StatsEditorialService extends \PKP\services\PKPStatsEditorialService
{
    /**
     * Process the seriesIds param when getting the query builder
     *
     * @param array $args
     */
    protected function getQueryBuilder($args = [])
    {
        $statsQB = parent::getQueryBuilder($args);
        if (!empty(($args['seriesIds']))) {
            $statsQB->filterBySections($args['seriesIds']);
        }
        return $statsQB;
    }

    protected function getAcceptedDecisions(): array
    {
        return [
            Decision::ACCEPT,
            Decision::ACCEPT_INTERNAL,
            Decision::SKIP_EXTERNAL_REVIEW,
            Decision::SEND_TO_PRODUCTION,
        ];
    }

    protected function getDeclinedDecisions(): array
    {
        return [
            Decision::DECLINE,
            Decision::INITIAL_DECLINE,
            Decision::DECLINE_INTERNAL,
        ];
    }
}
