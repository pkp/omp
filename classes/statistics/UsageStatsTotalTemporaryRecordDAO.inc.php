<?php

/**
 * @file classes/statistics/UsageStatsTotalTemporaryRecordDAO.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsTotalTemporaryRecordDAO
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding total book and chapter item metrics (investigations and requests).
 *
 * It considers:
 * context and catalog index page views,
 * series landing page views,
 * book abstract, primary and supp file views,
 * chapter landing page, chapter primary and supp file views,
 * geo submission usage,
 * COUNTER submission stats.
 */

namespace APP\statistics;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;

class UsageStatsTotalTemporaryRecordDAO
{
    /**
     * The name of the table. This table conteins all usage events.
     */
    public string $table = 'usage_stats_total_temporary_records';

    /**
     * Add the passed usage statistic record.
     *
     * @param object $entryData [
     * 	chapter_id
     *  time
     *  ip
     *  canonicalURL
     *  contextId
     *  submissionId
     *  representationId
     *  assocType
     *  assocId
     *  fileType
     *  userAgent
     *  country
     *  region
     *  city
     *  instituionIds
     * ]
     */
    public function insert(object $entryData, int $lineNumber, string $loadId): void
    {
        DB::table($this->table)->insert([
            'date' => $entryData->time,
            'ip' => $entryData->ip,
            'user_agent' => substr($entryData->userAgent, 0, 255),
            'line_number' => $lineNumber,
            'canonical_url' => $entryData->canonicalUrl,
            'chapter_id' => property_exists($entryData, 'chapterId') ? $entryData->chapterId : null,
            'context_id' => $entryData->contextId,
            'submission_id' => $entryData->submissionId,
            'representation_id' => $entryData->representationId,
            'assoc_type' => $entryData->assocType,
            'assoc_id' => $entryData->assocId,
            'file_type' => $entryData->fileType,
            'country' => !empty($entryData->country) ? $entryData->country : '',
            'region' => !empty($entryData->region) ? $entryData->region : '',
            'city' => !empty($entryData->city) ? $entryData->city : '',
            'load_id' => $loadId,
        ]);
    }

    public function checkForeignKeys(object $entryData): array
    {
        $errorMsg = [];
        if (DB::table('presses')->where('press_id', '=', $entryData->contextId)->doesntExist()) {
            $errorMsg[] = "press_id: {$entryData->contextId}";
        }
        if (!empty($entryData->chapterId) && DB::table('submission_chapters')->where('chapter_id', '=', $entryData->chapterId)->doesntExist()) {
            $errorMsg[] = "chapter_id: {$entryData->chapterId}";
        }
        if (!empty($entryData->submissionId) && DB::table('submissions')->where('submission_id', '=', $entryData->submissionId)->doesntExist()) {
            $errorMsg[] = "submission_id: {$entryData->submissionId}";
        }
        if (!empty($entryData->representationId) && DB::table('publication_formats')->where('publication_format_id', '=', $entryData->representationId)->doesntExist()) {
            $errorMsg[] = "publication_format_id: {$entryData->representationId}";
        }

        if (in_array($entryData->assocType, [Application::ASSOC_TYPE_SUBMISSION_FILE, Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER]) &&
            DB::table('submission_files')->where('submission_file_id', '=', $entryData->assocId)->doesntExist()) {
            $errorMsg[] = "submission_file_id: {$entryData->assocId}";
        }
        foreach ($entryData->institutionIds as $institutionId) {
            if (DB::table('institutions')->where('institution_id', '=', $institutionId)->doesntExist()) {
                $errorMsg[] = "institution_id: {$institutionId}";
            }
        }
        return $errorMsg;
    }

    /**
     * Delete all temporary records associated
     * with the passed load id.
     */
    public function deleteByLoadId(string $loadId): void
    {
        DB::table($this->table)->where('load_id', '=', $loadId)->delete();
    }

    /**
     * Remove Double Clicks
     * Remove the potential of over-counting which could occur when a user clicks the same link multiple times.
     * Double-clicks, i.e. two clicks in succession, on a link by the same user within a 30-second period MUST be counted as one action.
     * When two actions are made for the same URL within 30 seconds the first request MUST be removed and the second retained.
     * A user is identified by IP address combined with the browser’s user-agent.
     *
     * See https://www.projectcounter.org/code-of-practice-five-sections/7-processing-rules-underlying-counter-reporting-data/#doubleclick
     */
    public function removeDoubleClicks(int $counterDoubleClickTimeFilter): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} ust WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} ustt WHERE ustt.load_id = ust.load_id AND ustt.ip = ust.ip AND ustt.user_agent = ust.user_agent AND ustt.canonical_url = ust.canonical_url AND EXTRACT(EPOCH FROM (ustt.date - ust.date)) < ? AND EXTRACT(EPOCH FROM (ustt.date - ust.date)) > 0 AND ust.line_number < ustt.line_number) AS tmp)", [$counterDoubleClickTimeFilter]);
        } else {
            DB::statement("DELETE FROM {$this->table} ust WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} ustt WHERE ustt.load_id = ust.load_id AND ustt.ip = ust.ip AND ustt.user_agent = ust.user_agent AND ustt.canonical_url = ust.canonical_url AND TIMESTAMPDIFF(SECOND, ust.date, ustt.date) < ? AND TIMESTAMPDIFF(SECOND, ust.date, ustt.date) > 0 AND ust.line_number < ustt.line_number) AS tmp)", [$counterDoubleClickTimeFilter]);
        }
    }

    /**
     * Load usage for context and catalog index pages
     */
    public function loadMetricsContext(string $loadId): void
    {
        DB::table('metrics_context')->where('load_id', '=', $loadId)->delete();
        DB::statement(
            "
            INSERT INTO metrics_context (load_id, context_id, date, metric)
                SELECT load_id, context_id, DATE(date) as date, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, DATE(date)
            ",
            [$loadId, Application::getContextAssocType()]
        );
    }

    /**
     * Load usage for series landing pages
     */
    public function loadMetricsSeries(string $loadId): void
    {
        DB::table('metrics_series')->where('load_id', '=', $loadId)->delete();
        DB::statement(
            "
            INSERT INTO metrics_series (load_id, context_id, series_id, date, metric)
                SELECT load_id, context_id, assoc_id, DATE(date) as date, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, assoc_id, DATE(date)
            ",
            [$loadId, Application::ASSOC_TYPE_SERIES]
        );
    }

    /**
     * Load usage for submissions (abstract page, primary and supp files, chapter landing page views)
     */
    public function loadMetricsSubmission(string $loadId)
    {
        DB::table('metrics_submission')->where('load_id', '=', $loadId)->delete();
        // load abstract page views
        DB::statement(
            '
            INSERT INTO metrics_submission (load_id, context_id, submission_id, assoc_type, date, metric)
                SELECT load_id, context_id, submission_id, ' . Application::ASSOC_TYPE_SUBMISSION . ", DATE(date) as date, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, DATE(date)
            ",
            [$loadId, Application::ASSOC_TYPE_SUBMISSION]
        );
        // load primary files views
        DB::statement(
            '
            INSERT INTO metrics_submission (load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, date, metric)
                SELECT load_id, context_id, submission_id, representation_id, chapter_id, assoc_id, file_type, ' . Application::ASSOC_TYPE_SUBMISSION_FILE . ", DATE(date) as date, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, representation_id, chapter_id, assoc_id, file_type, DATE(date)
            ",
            [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]
        );
        // load supp files views
        DB::statement(
            '
            INSERT INTO metrics_submission (load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, date, metric)
                SELECT load_id, context_id, submission_id, representation_id, chapter_id, assoc_id, file_type, ' . Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER . ", DATE(date) as date, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, representation_id, chapter_id, assoc_id, file_type, DATE(date)
            ",
            [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER]
        );
        // load chapter landing page views
        DB::statement(
            '
            INSERT INTO metrics_submission (load_id, context_id, submission_id, chapter_id, assoc_type, date, metric)
            SELECT load_id, context_id, submission_id, chapter_id, ' . Application::ASSOC_TYPE_CHAPTER . ", DATE(date) as date, count(*) as metric
            FROM {$this->table}
            WHERE load_id = ? AND assoc_type = ?
            GROUP BY load_id, context_id, submission_id, chapter_id, DATE(date)
            ",
            [$loadId, Application::ASSOC_TYPE_CHAPTER]
        );
    }

    // For the DB tables that contain also the unique metrics, this deletion by loadId is in a separate function,
    // differently to the deletion for the tables above (metrics_context, metrics_series and metrics_submission).
    // The total metrics will be loaded in this class (s. load... functions below),
    // unique metrics are loaded in UnsageStatsUnique... classes
    public function deleteSubmissionGeoDailyByLoadId(string $loadId): void
    {
        DB::table('metrics_submission_geo_daily')->where('load_id', '=', $loadId)->delete();
    }
    public function deleteCounterSubmissionDailyByLoadId(string $loadId): void
    {
        DB::table('metrics_counter_submission_daily')->where('load_id', '=', $loadId)->delete();
    }
    public function deleteCounterSubmissionInstitutionDailyByLoadId(string $loadId): void
    {
        DB::table('metrics_counter_submission_institution_daily')->where('load_id', '=', $loadId)->delete();
    }

    /**
     * Load total geographical usage on the submission level
     */
    public function loadMetricsSubmissionGeoDaily(string $loadId): void
    {
        // construct metric upsert
        $metricUpsertSql = "
            INSERT INTO metrics_submission_geo_daily (load_id, context_id, submission_id, date, country, region, city, metric, metric_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, country, region, city, count(*) as metric_tmp, 0 as metric_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND (country <> '' OR region <> '' OR city <> '')
                GROUP BY load_id, context_id, submission_id, DATE(date), country, region, city) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_geo_daily_uc_load_id_context_id_submission_id_country_region_city_date DO UPDATE
                SET metric = excluded.metric;
                ';
        } else {
            $metricUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric = metric_tmp;
                ';
        }
        // load metric
        DB::statement($metricUpsertSql, [$loadId]);
    }

    /**
     * Load total COUNTER submission (book and chapter) usage (investigations and requests)
     */
    public function loadMetricsCounterSubmissionDaily(string $loadId): void
    {
        // construct metric_book_investigations upsert
        $metricBookInvestigationsUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, count(*) as metric, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_investigations = excluded.metric_book_investigations;
                ';
        } else {
            $metricBookInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations = metric;
                ';
        }
        // load metric_book_investigations
        DB::statement($metricBookInvestigationsUpsertSql, [$loadId]);

        // construct metric_book_requests upsert
        $metricBookRequestsUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, count(*) as metric, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ? AND chapter_id IS NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookRequestsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_requests = excluded.metric_book_requests;
                ';
        } else {
            $metricBookRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_requests = metric;
                ';
        }
        // load metric_book_requests
        DB::statement($metricBookRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);

        // construct metric_chapter_investigations upsert
        $metricChapterInvestigationsUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, count(*) as metric, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_investigations = excluded.metric_chapter_investigations;
                ';
        } else {
            $metricChapterInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations = metric;
                ';
        }
        // load metric_chapter_investigations
        DB::statement($metricChapterInvestigationsUpsertSql, [$loadId]);

        // construct metric_chapter_requests upsert
        $metricChapterRequestsUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, count(*) as metric, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ? AND chapter_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterRequestsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_requests = excluded.metric_chapter_requests;
                ';
        } else {
            $metricChapterRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests = metric;
                ';
        }
        // load metric_chapter_requests
        DB::statement($metricChapterRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    /**
     * Load total institutional COUNTER submission (book and chapter) usage (investigations and requests)
     */
    public function loadMetricsCounterSubmissionInstitutionDaily(string $loadId): void
    {
        // construct metric_book_investigations upsert
        $metricBookInvestigationsUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
                SELECT * FROM (
                    SELECT ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date) as date, usit.institution_id, count(*) as metric, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                    FROM {$this->table} ustt
                    JOIN usage_stats_institution_temporary_records usit on (usit.load_id = ustt.load_id AND usit.line_number = ustt.line_number)
                    WHERE ustt.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL AND usit.institution_id = ?
                    GROUP BY ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date), usit.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_book_investigations = excluded.metric_book_investigations;
                ';
        } else {
            $metricBookInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations = metric;
                ';
        }

        // construct metric_book_requests upsert
        $metricBookRequestsUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date) as date, usit.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, count(*) as metric, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} ustt
                JOIN usage_stats_institution_temporary_records usit on (usit.load_id = ustt.load_id AND usit.line_number = ustt.line_number)
                WHERE ustt.load_id = ? AND ustt.assoc_type = ? AND chapter_id IS NULL AND usit.institution_id = ?
                GROUP BY ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date), usit.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookRequestsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_book_requests = excluded.metric_book_requests;
                ';
        } else {
            $metricBookRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_requests = metric;
                ';
        }

        // construct metric_chapter_investigations upsert
        $metricChapterInvestigationsUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
                SELECT * FROM (
                    SELECT ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date) as date, usit.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, count(*) as metric, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                    FROM {$this->table} ustt
                    JOIN usage_stats_institution_temporary_records usit on (usit.load_id = ustt.load_id AND usit.line_number = ustt.line_number)
                    WHERE ustt.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL AND usit.institution_id = ?
                    GROUP BY ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date), usit.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_investigations = excluded.metric_chapter_investigations;
                ';
        } else {
            $metricChapterInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations = metric;
                ';
        }

        // construct metric_chapter_requests upsert
        $metricChapterRequestsUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date) as date, usit.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, count(*) as metric, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} ustt
                JOIN usage_stats_institution_temporary_records usit on (usit.load_id = ustt.load_id AND usit.line_number = ustt.line_number)
                WHERE ustt.load_id = ? AND ustt.assoc_type = ? AND chapter_id IS NOT NULL AND usit.institution_id = ?
                GROUP BY load_id, context_id, submission_id, DATE(date), institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterRequestsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_requests = excluded.metric_chapter_requests;
                ';
        } else {
            $metricChapterRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests = metric;
                ';
        }

        $statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var UsageStatsInstitutionTemporaryRecordDAO $statsInstitutionDao */
        $institutionIds = $statsInstitutionDao->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            // load metric_book_investigations
            DB::statement($metricBookInvestigationsUpsertSql, [$loadId, (int) $institutionId]);
            // load metric_book_requests
            DB::statement($metricBookRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
            // load metric_chapter_investigations
            DB::statement($metricChapterInvestigationsUpsertSql, [$loadId, (int) $institutionId]);
            // load metric_chapter_requests
            DB::statement($metricChapterRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
        }
    }
}
