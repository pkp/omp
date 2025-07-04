<?php

/**
 * @file classes/statistics/StatisticsHelper.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatisticsHelper
 *
 * @ingroup statistics
 *
 * @brief Statistics helper class.
 *
 */

namespace APP\statistics;

use PKP\statistics\PKPStatisticsHelper;

class StatisticsHelper extends PKPStatisticsHelper
{
    // Metrics:
    public const STATISTICS_METRIC_BOOK_INVESTIGATIONS = 'metric_book_investigations';
    public const STATISTICS_METRIC_BOOK_INVESTIGATIONS_UNIQUE = 'metric_book_investigations_unique';
    public const STATISTICS_METRIC_BOOK_REQUESTS = 'metric_book_requests';
    public const STATISTICS_METRIC_BOOK_REQUESTS_UNIQUE = 'metric_book_requests_unique';
    public const STATISTICS_METRIC_CHAPTER_INVESTIGATIONS = 'metric_chapter_investigations';
    public const STATISTICS_METRIC_CHAPTER_INVESTIGATIONS_UNIQUE = 'metric_chapter_investigations_unique';
    public const STATISTICS_METRIC_CHAPTER_REQUESTS = 'metric_chapter_requests';
    public const STATISTICS_METRIC_CHAPTER_REQUESTS_UNIQUE = 'metric_chapter_requests_unique';
    public const STATISTICS_METRIC_TITLE_INVESTIGATIONS_UNIQUE = 'metric_title_investigations_unique';
    public const STATISTICS_METRIC_TITLE_REQUESTS_UNIQUE = 'metric_title_requests_unique';

    /**
     * COUNTER DB tables metrics columns
     */
    public static function getCounterMetricsColumns(): array
    {
        return [
            self::STATISTICS_METRIC_BOOK_INVESTIGATIONS,
            self::STATISTICS_METRIC_BOOK_INVESTIGATIONS_UNIQUE,
            self::STATISTICS_METRIC_BOOK_REQUESTS,
            self::STATISTICS_METRIC_BOOK_REQUESTS_UNIQUE,
            self::STATISTICS_METRIC_CHAPTER_INVESTIGATIONS,
            self::STATISTICS_METRIC_CHAPTER_INVESTIGATIONS_UNIQUE,
            self::STATISTICS_METRIC_CHAPTER_REQUESTS,
            self::STATISTICS_METRIC_CHAPTER_REQUESTS_UNIQUE,
            self::STATISTICS_METRIC_TITLE_INVESTIGATIONS_UNIQUE,
            self::STATISTICS_METRIC_TITLE_REQUESTS_UNIQUE
        ];
    }
}
