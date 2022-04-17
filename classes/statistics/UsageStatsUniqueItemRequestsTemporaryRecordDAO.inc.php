<?php

/**
 * @file classes/statistics/UsageStatsUniqueItemRequestsTemporaryRecordDAO.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsUniqueItemRequestsTemporaryRecordDAO
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique book and chapter item requests (primary files downloads).
 */

namespace APP\statistics;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;

class UsageStatsUniqueItemRequestsTemporaryRecordDAO
{
    /**
     * The name of the table.
     * This table contains all book and chapter item requests (primary files downloads).
     */
    public string $table = 'usage_stats_unique_item_requests_temporary_records';

    /**
     * Add the passed usage statistic record.
     *
     * @param object $entryData [
     * 	chapter_id
     *  time
     *  ip
     *  canonicalUrl
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
            'context_id' => $entryData->contextId,
            'submission_id' => $entryData->submissionId,
            'chapter_id' => !empty($entryData->chapterId) ? $entryData->chapterId : null,
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

    /**
     * Delete all temporary records associated
     * with the passed load id.
     */
    public function deleteByLoadId(string $loadId): void
    {
        DB::table($this->table)->where('load_id', '=', $loadId)->delete();
    }

    /**
     * Remove Unique Clicks
     * If multiple transactions represent the same item and occur in the same user-sessions, only one unique activity MUST be counted for that item.
     * Unique book item is a submission (with chapter ID = NULL).
     * Unique chapter item is a chapter.
     * A user session is defined by the combination of IP address + user agent + transaction date + hour of day.
     * Only the last unique activity will be retained (and thus counted), all the other will be removed.
     *
     * See https://www.projectcounter.org/code-of-practice-five-sections/7-processing-rules-underlying-counter-reporting-data/#counting
     */
    public function removeBookItemUniqueClicks(): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} usur WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usurt WHERE usurt.load_id = usur.load_id AND usurt.ip = usur.ip AND usurt.user_agent = usur.user_agent AND usurt.context_id = usur.context_id AND usurt.submission_id = usur.submission_id AND usurt.chapter_id IS NULL AND usur.chapter_id IS NULL AND EXTRACT(HOUR FROM usurt.date) = EXTRACT(HOUR FROM usur.date) AND usur.line_number < usurt.line_number) AS tmp)");
        } else {
            DB::statement("DELETE FROM {$this->table} usur WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usurt WHERE usurt.load_id = usur.load_id AND usurt.ip = usur.ip AND usurt.user_agent = usur.user_agent AND usurt.context_id = usur.context_id AND usurt.submission_id = usur.submission_id AND usurt.chapter_id IS NULL AND usur.chapter_id IS NULL AND TIMESTAMPDIFF(HOUR, usur.date, usurt.date) = 0 AND usur.line_number < usurt.line_number) AS tmp)");
        }
    }
    public function removeChapterItemUniqueClicks(): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} usur WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usurt WHERE usurt.load_id = usur.load_id AND usurt.ip = usur.ip AND usurt.user_agent = usur.user_agent AND usurt.context_id = usur.context_id AND usurt.submission_id = usur.submission_id AND usurt.chapter_id = usur.chapter_id AND usurt.chapter_id IS NOT NULL AND EXTRACT(HOUR FROM usurt.date) = EXTRACT(HOUR FROM usur.date) AND usur.line_number < usurt.line_number) AS tmp)");
        } else {
            DB::statement("DELETE FROM {$this->table} usur WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usurt WHERE usurt.load_id = usur.load_id AND usurt.ip = usur.ip AND usurt.user_agent = usur.user_agent AND usurt.context_id = usur.context_id AND usurt.submission_id = usur.submission_id AND usurt.chapter_id = usur.chapter_id AND usurt.chapter_id IS NOT NULL AND TIMESTAMPDIFF(HOUR, usur.date, usurt.date) = 0 AND usur.line_number < usurt.line_number) AS tmp)");
        }
    }

    /**
     * Load unique COUNTER item (book and chapter) requests (primary files downloads)
     */
    public function loadMetricsCounterSubmissionDaily(string $loadId): void
    {
        // construct metric_book_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricBookRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, count(*) as metric, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ? AND chapter_id IS NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_requests_unique = excluded.metric_book_requests_unique;
                ';
        } else {
            $metricBookRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_requests_unique = metric;
                ';
        }
        // load metric_book_requests_unique
        DB::statement($metricBookRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);

        // construct metric_chapter_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricChapterRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, count(*) as metric, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ? AND chapter_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_requests_unique = excluded.metric_chapter_requests_unique;
                ';
        } else {
            $metricChapterRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests_unique = metric;
                ';
        }
        // load metric_chapter_requests_unique
        DB::statement($metricChapterRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    /**
     * Load unique institutional COUNTER item (book and chapter) requests (primary files downloads)
     */
    public function loadMetricsCounterSubmissionInstitutionDaily(string $loadId): void
    {
        // construct metric_book_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricBookRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
                SELECT * FROM (
                    SELECT usur.load_id, usur.context_id, usur.submission_id, DATE(usur.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, count(*) as metric, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                    FROM {$this->table} usur
                    JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usur.load_id AND usi.line_number = usur.line_number)
                    WHERE usur.load_id = ? AND usur.assoc_type = ? AND chapter_id IS NULL AND usi.institution_id = ?
                    GROUP BY usur.load_id, usur.context_id, usur.submission_id, DATE(usur.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_book_requests_unique = excluded.metric_book_requests_unique;
                ';
        } else {
            $metricBookRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_requests_unique = metric;
                ';
        }

        // construct metric_chapter_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricChapterRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usucr.load_id, usucr.context_id, usucr.submission_id, DATE(usucr.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, count(*) as metric, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} usucr
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usucr.load_id AND usi.line_number = usucr.line_number)
                WHERE usucr.load_id = ? AND usucr.assoc_type = ? AND chapter_id IS NOT NULL AND usi.institution_id = ?
                GROUP BY usucr.load_id, usucr.context_id, usucr.submission_id, DATE(usucr.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_requests_unique = excluded.metric_chapter_requests_unique;
                ';
        } else {
            $metricChapterRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests_unique = metric;
                ';
        }

        $statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var UsageStatsInstitutionTemporaryRecordDAO $statsInstitutionDao */
        $institutionIds = $statsInstitutionDao->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            // load metric_book_requests_unique
            DB::statement($metricBookRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
            // load metric_chapter_requests_unique
            DB::statement($metricChapterRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
        }
    }
}
