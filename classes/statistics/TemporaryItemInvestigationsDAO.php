<?php

/**
 * @file classes/statistics/TemporaryItemInvestigationsDAO.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TemporaryItemInvestigationsDAO
 *
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique book and chapter item investigations (abstract, primary and supp file views).
 */

namespace APP\statistics;

use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\statistics\PKPTemporaryItemInvestigationsDAO;
use PKP\statistics\TemporaryInstitutionsDAO;

class TemporaryItemInvestigationsDAO extends PKPTemporaryItemInvestigationsDAO
{
    /**
     * Get Laravel optimized array of data to insert into the table based on the log entry
     */
    protected function getInsertData(object $entryData): array
    {
        return array_merge(
            parent::getInsertData($entryData),
            ['chapter_id' => $entryData->chapterId]
        );
    }

    /**
     * Remove Unique Clicks for book and chapter items
     * If multiple transactions represent the same item and occur in the same user-sessions, only one unique activity MUST be counted for that item.
     * Unique book item is a submission (with chapter ID = NULL).
     * Unique chapter item is a chapter.
     * A user session is defined by the combination of IP address + user agent + transaction date + hour of day.
     * Only the last unique activity will be retained (and thus counted), all the other will be removed.
     *
     * See https://www.projectcounter.org/code-of-practice-five-sections/7-processing-rules-underlying-counter-reporting-data/#counting
     */
    public function compileBookItemUniqueClicks(string $loadId): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement(
                "
                DELETE FROM {$this->table} usui
                WHERE EXISTS (
                    SELECT * FROM (
                        SELECT 1 FROM {$this->table} usuit
                        WHERE usui.load_id = ? AND usuit.load_id = usui.load_id AND
                            usuit.context_id = usui.context_id AND
                            usuit.ip = usui.ip AND
                            usuit.user_agent = usui.user_agent AND
                            usuit.submission_id = usui.submission_id AND
                            usuit.chapter_id IS NULL AND usui.chapter_id IS NULL AND
                            EXTRACT(HOUR FROM usuit.date) = EXTRACT(HOUR FROM usui.date) AND
                            usui.line_number < usuit.line_number
                    ) AS tmp
                )
                ",
                [$loadId]
            );
        } else {
            DB::statement(
                "
                DELETE FROM usui USING {$this->table} usui
                INNER JOIN {$this->table} usuit ON (
                    usuit.load_id = usui.load_id AND
                    usuit.context_id = usui.context_id AND
                    usuit.ip = usui.ip AND
                    usuit.user_agent = usui.user_agent AND
                    usuit.submission_id = usui.submission_id
                )
                WHERE usui.load_id = ? AND
                    usuit.chapter_id IS NULL AND usui.chapter_id IS NULL AND
                    TIMESTAMPDIFF(HOUR, usui.date, usuit.date) = 0 AND
                    usui.line_number < usuit.line_number
                ",
                [$loadId]
            );
        }
    }
    public function compileChapterItemUniqueClicks(string $loadId): void
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement(
                "
                DELETE FROM {$this->table} usui
                WHERE EXISTS (
                    SELECT * FROM (
                        SELECT 1 FROM {$this->table} usuit
                        WHERE usuit.load_id = ? AND usuit.load_id = usui.load_id AND
                            usuit.context_id = usui.context_id AND
                            usuit.ip = usui.ip AND
                            usuit.user_agent = usui.user_agent AND
                            usuit.submission_id = usui.submission_id AND
                            usuit.chapter_id = usui.chapter_id AND usuit.chapter_id IS NOT NULL AND
                            EXTRACT(HOUR FROM usuit.date) = EXTRACT(HOUR FROM usui.date) AND
                            usui.line_number < usuit.line_number
                    ) AS tmp
                )
                ",
                [$loadId]
            );
        } else {
            DB::statement(
                "
                DELETE FROM usui USING {$this->table} usui
                INNER JOIN {$this->table} usuit ON (
                    usuit.load_id = usui.load_id AND
                    usuit.context_id = usui.context_id AND
                    usuit.ip = usui.ip AND
                    usuit.user_agent = usui.user_agent AND
                    usuit.submission_id = usui.submission_id AND
                    usuit.chapter_id = usui.chapter_id
                )
                WHERE usuit.load_id = ? AND
                    usuit.chapter_id IS NOT NULL AND
                    TIMESTAMPDIFF(HOUR, usui.date, usuit.date) = 0 AND
                    usui.line_number < usuit.line_number
                ",
                [$loadId]
            );
        }
    }

    /**
     * Load unique COUNTER item (book and chapter) investigations
     */
    public function compileCounterSubmissionDailyMetrics(string $loadId): void
    {
        // construct metric_book_investigations_unique upsert
        $metricBookInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, count(*) as metric, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_investigations_unique = excluded.metric_book_investigations_unique;
                ';
        } else {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations_unique = metric;
                ';
        }
        DB::statement($metricBookInvestigationsUniqueUpsertSql, [$loadId]);

        // construct metric_chapter_investigations_unique upsert
        $metricChapterInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, count(*) as metric, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msd_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_investigations_unique = excluded.metric_chapter_investigations_unique;
                ';
        } else {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations_unique = metric;
                ';
        }
        DB::statement($metricChapterInvestigationsUniqueUpsertSql, [$loadId]);
    }

    /**
     * Load unique institutional COUNTER item (book and chapter9 investigations
     */
    public function compileCounterSubmissionInstitutionDailyMetrics(string $loadId): void
    {
        // construct metric_book_investigations_unique upsert
        $metricBookInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usui.load_id, usui.context_id, usui.submission_id, DATE(usui.date) as date, usi.institution_id, 0 as metric_book_investigations, count(*) as metric, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} usui
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usui.load_id AND usi.line_number = usui.line_number)
                WHERE usui.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL AND usi.institution_id = ?
                GROUP BY usui.load_id, usui.context_id, usui.submission_id, DATE(usui.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_book_investigations_unique = excluded.metric_book_investigations_unique;
                ';
        } else {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations_unique = metric;
                ';
        }

        // construct metric_chapter_investigations_unique upsert
        $metricChapterInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usuci.load_id, usuci.context_id, usuci.submission_id, DATE(usuci.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, count(*) as metric, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} usuci
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usuci.load_id AND usi.line_number = usuci.line_number)
                WHERE usuci.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL AND usi.institution_id = ?
                GROUP BY usuci.load_id, usuci.context_id, usuci.submission_id, DATE(usuci.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT msid_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_investigations_unique = excluded.metric_chapter_investigations_unique;
                ';
        } else {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations_unique = metric;
                ';
        }

        $temporaryInstitutionsDAO = DAORegistry::getDAO('TemporaryInstitutionsDAO'); /** @var TemporaryInstitutionsDAO $temporaryInstitutionsDAO */
        $institutionIds = $temporaryInstitutionsDAO->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            DB::statement($metricBookInvestigationsUniqueUpsertSql, [$loadId, (int) $institutionId]);
            DB::statement($metricChapterInvestigationsUniqueUpsertSql, [$loadId, (int) $institutionId]);
        }
    }
}
