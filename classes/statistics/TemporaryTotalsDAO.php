<?php

/**
 * @file classes/statistics/TemporaryTotalsDAO.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TemporaryTotalsDAO
 *
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
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\statistics\PKPTemporaryTotalsDAO;
use PKP\statistics\TemporaryInstitutionsDAO;

class TemporaryTotalsDAO extends PKPTemporaryTotalsDAO
{
    /**
     * Get Laravel optimized array of data to insert into the table based on the log entry
     */
    protected function getInsertData(object $entryData): array
    {
        return array_merge(
            parent::getInsertData($entryData),
            [
                'chapter_id' => $entryData->chapterId,
                'series_id' => $entryData->seriesId,
            ]
        );
    }

    /**
     * Load usage for series landing pages
     */
    public function compileSeriesMetrics(string $loadId): void
    {
        $date = DateTimeImmutable::createFromFormat('Ymd', substr($loadId, -12, 8));
        DB::table('metrics_series')->where('load_id', '=', $loadId)->orWhereDate('date', '=', $date)->delete();
        $selectSeriesMetrics = DB::table($this->table)
            ->select(DB::raw('load_id, context_id, series_id, DATE(date) as date, count(*) as metric'))
            ->where('load_id', '=', $loadId)
            ->where('assoc_type', '=', Application::ASSOC_TYPE_SERIES)
            ->groupBy(DB::raw('load_id, context_id, series_id, DATE(date)'));
        DB::table('metrics_series')->insertUsing(['load_id', 'context_id', 'series_id', 'date', 'metric'], $selectSeriesMetrics);
    }

    /**
     * Load usage for submissions (abstract page, primary fiels, supp files, chapter landing page)
     */
    public function compileSubmissionMetrics(string $loadId): void
    {
        $date = DateTimeImmutable::createFromFormat('Ymd', substr($loadId, -12, 8));
        DB::table('metrics_submission')->where('load_id', '=', $loadId)->orWhereDate('date', '=', $date)->delete();
        $selectSubmissionMetrics = DB::table($this->table)
            ->select(DB::raw('load_id, context_id, submission_id, assoc_type, DATE(date) as date, count(*) as metric'))
            ->where('load_id', '=', $loadId)
            ->where('assoc_type', '=', Application::ASSOC_TYPE_SUBMISSION)
            ->groupBy(DB::raw('load_id, context_id, submission_id, assoc_type, DATE(date)'));
        DB::table('metrics_submission')->insertUsing(['load_id', 'context_id', 'submission_id', 'assoc_type', 'date', 'metric'], $selectSubmissionMetrics);

        $selectSubmissionFileMetrics = DB::table($this->table)
            ->select(DB::raw('load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, DATE(date) as date, count(*) as metric'))
            ->where('load_id', '=', $loadId)
            ->where('assoc_type', '=', Application::ASSOC_TYPE_SUBMISSION_FILE)
            ->groupBy(DB::raw('load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, DATE(date)'));
        DB::table('metrics_submission')->insertUsing(['load_id', 'context_id', 'submission_id', 'representation_id', 'chapter_id', 'submission_file_id', 'file_type', 'assoc_type', 'date', 'metric'], $selectSubmissionFileMetrics);

        $selectSubmissionSuppFileMetrics = DB::table($this->table)
            ->select(DB::raw('load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, DATE(date) as date, count(*) as metric'))
            ->where('load_id', '=', $loadId)
            ->where('assoc_type', '=', Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER)
            ->groupBy(DB::raw('load_id, context_id, submission_id, representation_id, chapter_id, submission_file_id, file_type, assoc_type, DATE(date)'));
        DB::table('metrics_submission')->insertUsing(['load_id', 'context_id', 'submission_id', 'representation_id', 'chapter_id', 'submission_file_id', 'file_type', 'assoc_type', 'date', 'metric'], $selectSubmissionSuppFileMetrics);

        $selectChapterMetrics = DB::table($this->table)
            ->select(DB::raw('load_id, context_id, submission_id, chapter_id, assoc_type, DATE(date) as date, count(*) as metric'))
            ->where('load_id', '=', $loadId)
            ->where('assoc_type', '=', Application::ASSOC_TYPE_CHAPTER)
            ->groupBy(DB::raw('load_id, context_id, submission_id, chapter_id, assoc_type, DATE(date)'));
        DB::table('metrics_submission')->insertUsing(['load_id', 'context_id', 'submission_id', 'chapter_id', 'assoc_type', 'date', 'metric'], $selectChapterMetrics);
    }

    /**
     * Load total COUNTER submission (book and chapter) usage (investigations and requests)
     */
    public function compileCounterSubmissionDailyMetrics(string $loadId): void
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
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_investigations = excluded.metric_book_investigations;
                ';
        } else {
            $metricBookInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations = metric;
                ';
        }
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
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_requests = excluded.metric_book_requests;
                ';
        } else {
            $metricBookRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_requests = metric;
                ';
        }
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
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_investigations = excluded.metric_chapter_investigations;
                ';
        } else {
            $metricChapterInvestigationsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations = metric;
                ';
        }
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
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_requests = excluded.metric_chapter_requests;
                ';
        } else {
            $metricChapterRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests = metric;
                ';
        }
        DB::statement($metricChapterRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    /**
     * Load total institutional COUNTER submission (book and chapter) usage (investigations and requests)
     */
    public function compileCounterSubmissionInstitutionDailyMetrics(string $loadId): void
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
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
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
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
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
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
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
                GROUP BY ustt.load_id, ustt.context_id, ustt.submission_id, DATE(ustt.date), usit.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterRequestsUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_requests = excluded.metric_chapter_requests;
                ';
        } else {
            $metricChapterRequestsUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_requests = metric;
                ';
        }

        $temporaryInstitutionsDAO = DAORegistry::getDAO('TemporaryInstitutionsDAO'); /** @var TemporaryInstitutionsDAO $temporaryInstitutionsDAO */
        $institutionIds = $temporaryInstitutionsDAO->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            DB::statement($metricBookInvestigationsUpsertSql, [$loadId, (int) $institutionId]);
            DB::statement($metricBookRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
            DB::statement($metricChapterInvestigationsUpsertSql, [$loadId, (int) $institutionId]);
            DB::statement($metricChapterRequestsUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
        }
    }
}
