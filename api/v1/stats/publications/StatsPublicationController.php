<?php

/**
 * @file api/v1/stats/publications/StatsPublicationController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsPublicationController
 *
 * @ingroup api_v1_stats
 *
 * @brief Handle API requests for publication statistics.
 *
 */

namespace APP\API\v1\stats\publications;

class StatsPublicationController extends \PKP\API\v1\stats\publications\PKPStatsPublicationController
{
    /** @var string The name of the section ids query param for this application */
    public $sectionIdsQueryParam = 'seriesIds';

    public function getSectionIdsQueryParam()
    {
        return $this->sectionIdsQueryParam;
    }
}
