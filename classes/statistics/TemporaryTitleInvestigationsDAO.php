<?php

/**
 * @file classes/statistics/TemporaryTitleInvestigationsDAO.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TemporaryTitleInvestigationsDAO
 *
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique title (submission) investigations (book and chapter abstract, primary and supp files views).
 */

namespace APP\statistics;

use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\statistics\TemporaryInstitutionsDAO;

class TemporaryTitleInvestigationsDAO
{
    /**
     * The name of the table.
     * This table contains all usage (clicks) for a title (submission),
     * considering book abd chapter abstract, primary and supp file views.
     */
    public string $table = 'usage_stats_unique_title_investigations_temporary_records';

    /**
     * Add the passed usage statistic record.
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
            'chapter_id' => $entryData->chapterId,
            'representation_id' => $entryData->representationId,
            'submission_file_id' => $entryData->submissionFileId,
            'assoc_type' => $entryData->assocType,
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
     * Remove Unique Title Clicks
     * If multiple transactions represent the same title and occur in the same user-sessions, only one unique activity MUST be counted for that item.
     * A title represents the parent work that the item is part of.
     * Unique title is a submission.
     * A user session is defined by the combination of IP address + user agent + transaction date + hour of day.
     * Only the last unique activity will be retained (and thus counted), all the other will be removed.
     *
     * See https://www.projectcounter.org/code-of-practice-five-sections/7-processing-rules-underlying-counter-reporting-data/#titles
     */
    public function compileTitleUniqueClicks(string $loadId): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement(
                "
                DELETE FROM {$this->table} usuti
                WHERE EXISTS (
                    SELECT * FROM (
                        SELECT 1 FROM {$this->table} usutit
                        WHERE usuti.load_id = ? AND usutit.load_id = usuti.load_id AND
                            usutit.context_id = usuti.context_id AND
                            usutit.ip = usuti.ip AND
                            usutit.user_agent = usuti.user_agent AND
                            usutit.submission_id = usuti.submission_id AND
                            EXTRACT(HOUR FROM usutit.date) = EXTRACT(HOUR FROM usuti.date) AND
                            usuti.line_number < usutit.line_number
                        ) AS tmp
                    )
                ",
                [$loadId]
            );
        } else {
            DB::statement(
                "
                DELETE FROM usuti USING {$this->table} usuti
                INNER JOIN {$this->table} usutit ON (
                    usutit.load_id = usuti.load_id AND
                    usutit.context_id = usuti.context_id AND
                    usutit.ip = usuti.ip AND
                    usutit.user_agent = usuti.user_agent AND
                    usutit.submission_id = usuti.submission_id)
                WHERE usuti.load_id = ? AND
                    TIMESTAMPDIFF(HOUR, usuti.date, usutit.date) = 0 AND
                    usuti.line_number < usutit.line_number
                ",
                [$loadId]
            );
        }
    }

    /**
     * Load unique geographical usage on the submission level
     */
    public function compileSubmissionGeoDailyMetrics(string $loadId): void
    {
        // construct metric_unique upsert
        $metricUniqueUpsertSql = "
            INSERT INTO metrics_submission_geo_daily (load_id, context_id, submission_id, date, country, region, city, metric, metric_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, country, region, city, 0 as metric, count(*) as metric_unique_tmp
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND (country <> '' OR region <> '' OR city <> '')
                GROUP BY load_id, context_id, submission_id, DATE(date), country, region, city) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msgd_uc_load_context_submission_c_r_c_date DO UPDATE
                SET metric_unique = excluded.metric_unique;
                ';
        } else {
            $metricUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_unique = metric_unique_tmp;
                ';
        }
        // load metric_unique
        DB::statement($metricUniqueUpsertSql, [$loadId]);
    }

    /**
     * Load unique COUNTER title (submission) investigations
     */
    public function compileCounterSubmissionDailyMetrics(string $loadId): void
    {
        // construct metric_title_investigations_unique upsert
        $metricTitleInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, count(*) as metric, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_title_investigations_unique = excluded.metric_title_investigations_unique;
                ';
        } else {
            $metricTitleInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_investigations_unique = metric;
                ';
        }
        DB::statement($metricTitleInvestigationsUniqueUpsertSql, [$loadId]);
    }

    /**
     * Load unique institutional COUNTER title (submission) investigations
     */
    public function compileCounterSubmissionInstitutionDailyMetrics(string $loadId): void
    {
        // construct metric_title_investigations_unique upsert
        $metricTitleInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
                SELECT * FROM (
                    SELECT usuti.load_id, usuti.context_id, usuti.submission_id, DATE(usuti.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, count(*) as metric, 0 as metric_title_requests_unique
                    FROM {$this->table} usuti
                    JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usuti.load_id AND usi.line_number = usuti.line_number)
                    WHERE usuti.load_id = ? AND submission_id IS NOT NULL AND usi.institution_id = ?
                    GROUP BY usuti.load_id, usuti.context_id, usuti.submission_id, DATE(usuti.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_title_investigations_unique = excluded.metric_title_investigations_unique;
                ';
        } else {
            $metricTitleInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_investigations_unique = metric;
                ';
        }

        $temporaryInstitutionsDAO = DAORegistry::getDAO('TemporaryInstitutionsDAO'); /** @var TemporaryInstitutionsDAO $temporaryInstitutionsDAO */
        $institutionIds = $temporaryInstitutionsDAO->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            DB::statement($metricTitleInvestigationsUniqueUpsertSql, [$loadId, (int) $institutionId]);
        }
    }
}
