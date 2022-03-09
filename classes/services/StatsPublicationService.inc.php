<?php
/**
 * @file classes/services/StatsPublicationService.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsPublicationService
 * @ingroup services
 *
 * @brief Helper class that encapsulates statistics business logic
 */

namespace APP\services;

use APP\core\Application;
use APP\facades\Repo;
use APP\submission\Submission;

class StatsPublicationService extends \PKP\services\PKPStatsPublicationService
{
    /**
     * A helper method to get the submissionIds param when a seriesIds
     * param is also passed.
     *
     * If the seriesIds and submissionIds params were both passed in the
     * request, then we only return IDs that match both conditions.
     *
     * @param array $seriesIds series IDs
     * @param ?array $submissionIds List of allowed submission IDs
     *
     * @return array submission IDs
     */
    public function processSectionIds(array $seriesIds, ?array $submissionIds): array
    {
        $seriesIdsSubmissionIds = Repo::submission()->getIds(
            Repo::submission()
                ->getCollector()
                ->filterByContextIds([Application::get()->getRequest()->getContext()->getId()])
                ->filterByStatus([Submission::STATUS_PUBLISHED])
                ->filterBySeriesIds($seriesIds)
        )->toArray();

        if ($submissionIds !== null && !empty($submissionIds)) {
            $submissionIds = array_intersect($submissionIds, $seriesIdsSubmissionIds);
        } else {
            $submissionIds = $seriesIdsSubmissionIds;
        }

        return $submissionIds;
    }
}
