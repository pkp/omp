<?php

/**
* @file classes/statistics/StatisticsHelper.inc.php
*
* Copyright (c) 2013-2021 Simon Fraser University
* Copyright (c) 2003-2021 John Willinsky
* Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
*
* @class StatisticsHelper
* @ingroup statistics
*
* @brief Statistics helper class.
*
*/

namespace APP\statistics;

use PKP\statistics\PKPStatisticsHelper;

class StatisticsHelper extends PKPStatisticsHelper
{
    // Give an OMP name to the section dimension.
    public const STATISTICS_DIMENSION_SERIES_ID = self::STATISTICS_DIMENSION_PKP_SECTION_ID;

    /**
     * @see PKPStatisticsHelper::getAppColumnTitle()
     */
    protected function getAppColumnTitle($column)
    {
        switch ($column) {
            case PKPStatisticsHelper::STATISTICS_DIMENSION_SUBMISSION_ID:
                return __('submission.monograph');
            case self::STATISTICS_DIMENSION_SERIES_ID:
                return __('series.series');
            case PKPStatisticsHelper::STATISTICS_DIMENSION_CONTEXT_ID:
                return __('context.context');
            default:
                assert(false);
        }
    }

    /**
     * @see PKPStatisticsHelper::getReportObjectTypesArray()
     */
    protected function getReportObjectTypesArray()
    {
        $objectTypes = parent::getReportObjectTypesArray();
        $objectTypes = $objectTypes + [
            ASSOC_TYPE_PRESS => __('context.context'),
            ASSOC_TYPE_SERIES => __('series.series'),
            ASSOC_TYPE_MONOGRAPH => __('submission.monograph'),
            ASSOC_TYPE_PUBLICATION_FORMAT => __('grid.catalogEntry.publicationFormatType')
        ];

        return $objectTypes;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\statistics\StatisticsHelper', '\StatisticsHelper');
    define('STATISTICS_DIMENSION_SERIES_ID', StatisticsHelper::STATISTICS_DIMENSION_SERIES_ID);
}
