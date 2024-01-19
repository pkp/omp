<?php

/**
 * @file classes/statistics/TemporaryTitleRequestsDAO.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TemporaryTitleRequestsDAO
 *
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique title (submission) requests (primary files downloads).
 */

namespace APP\statistics;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\statistics\TemporaryInstitutionsDAO;

class TemporaryTitleRequestsDAO
{
    /** This temporary table contains all (book and chapter) requests */
    /**
     * The name of the table.
     * This table contains all title (submission) requests (primary files downloads).
     */
    public string $table = 'usage_stats_unique_title_requests_temporary_records';

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
                DELETE FROM {$this->table} usutr
                WHERE EXISTS (
                    SELECT * FROM (
                        SELECT 1 FROM {$this->table} usutrt
                        WHERE usutr.load_id = ? AND usutrt.load_id = usutr.load_id AND
                            usutrt.context_id = usutr.context_id AND
                            usutrt.ip = usutr.ip AND
                            usutrt.user_agent = usutr.user_agent AND
                            usutrt.submission_id = usutr.submission_id AND
                            EXTRACT(HOUR FROM usutrt.date) = EXTRACT(HOUR FROM usutr.date) AND
                            usutr.line_number < usutrt.line_number
                    ) AS tmp
                )
                ",
                [$loadId]
            );
        } else {
            DB::statement(
                "
                DELETE FROM usutr USING {$this->table} usutr
                INNER JOIN {$this->table} usutrt ON (
                    usutrt.load_id = usutr.load_id AND
                    usutrt.context_id = usutr.context_id AND
                    usutrt.ip = usutr.ip AND
                    usutrt.user_agent = usutr.user_agent AND
                    usutrt.submission_id = usutr.submission_id
                )
                WHERE usutr.load_id = ? AND
                    TIMESTAMPDIFF(HOUR, usutr.date, usutrt.date) = 0 AND
                    usutr.line_number < usutrt.line_number
                ",
                [$loadId]
            );
        }
    }

    /**
     * Load unique COUNTER title (submission) requests (primary files downloads)
     */
    public function compileCounterSubmissionDailyMetrics(string $loadId): void
    {
        // construct metric_title_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricTitleRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_title_requests_unique = excluded.metric_title_requests_unique;
                ';
        } else {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_requests_unique = metric;
                ';
        }
        DB::statement($metricTitleRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    /**
     * Load unique institutional COUNTER title (submission) requests (primary files downloads)
     */
    public function compileCounterSubmissionInstitutionDailyMetrics(string $loadId): void
    {
        // construct metric_title_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricTitleRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usutr.load_id, usutr.context_id, usutr.submission_id, DATE(usutr.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, count(*) as metric
                FROM {$this->table} usutr
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usutr.load_id AND usi.line_number = usutr.line_number)
                WHERE usutr.load_id = ? AND usutr.assoc_type = ? AND usi.institution_id = ?
                GROUP BY usutr.load_id, usutr.context_id, usutr.submission_id, DATE(usutr.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_title_requests_unique = excluded.metric_title_requests_unique;
                ';
        } else {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_requests_unique = metric;
                ';
        }

        $temporaryInstitutionsDAO = DAORegistry::getDAO('TemporaryInstitutionsDAO'); /** @var TemporaryInstitutionsDAO $temporaryInstitutionsDAO */
        $institutionIds = $temporaryInstitutionsDAO->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            DB::statement($metricTitleRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
        }
    }
}
