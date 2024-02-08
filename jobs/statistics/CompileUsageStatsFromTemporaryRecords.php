<?php

/**
 * @file jobs/statistics/CompileUsageStatsFromTemporaryRecords.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see
 *  the file docs/COPYING.
 *
 * @class CompileUsageStatsFromTemporaryRecords
 *
 * @ingroup jobs
 *
 * @brief Class to handle the usage metrics data loading as a Job
 *
 * @deprecated 3.4.0.5
 */

namespace APP\jobs\statistics;

use APP\statistics\StatisticsHelper;
use APP\statistics\TemporaryItemInvestigationsDAO;
use APP\statistics\TemporaryItemRequestsDAO;
use APP\statistics\TemporaryTitleInvestigationsDAO;
use APP\statistics\TemporaryTitleRequestsDAO;
use APP\statistics\TemporaryTotalsDAO;
use PKP\db\DAORegistry;
use PKP\job\exceptions\JobException;
use PKP\jobs\BaseJob;
use PKP\statistics\TemporaryInstitutionsDAO;
use PKP\task\FileLoader;

class CompileUsageStatsFromTemporaryRecords extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param string $loadId Usage stats log file name
     */
    public function __construct(protected string $loadId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $compileSuccessful = $this->compileMetrics();
        if (!$compileSuccessful) {
            // Move the archived file back to staging
            $filename = $this->loadId;
            $archivedFilePath = StatisticsHelper::getUsageStatsDirPath() . DIRECTORY_SEPARATOR . FileLoader::FILE_LOADER_PATH_ARCHIVE . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($archivedFilePath)) {
                $filename .= '.gz';
                $archivedFilePath = StatisticsHelper::getUsageStatsDirPath() . DIRECTORY_SEPARATOR . FileLoader::FILE_LOADER_PATH_ARCHIVE . DIRECTORY_SEPARATOR . $filename;
            }
            $stagingPath = StatisticsHelper::getUsageStatsDirPath() . DIRECTORY_SEPARATOR . FileLoader::FILE_LOADER_PATH_STAGING . DIRECTORY_SEPARATOR . $filename;

            if (!rename($archivedFilePath, $stagingPath)) {
                $message = __('usageStats.compileMetrics.returnToStaging.error', ['filename' => $filename,
                    'archivedFilePath' => $archivedFilePath, 'stagingPath' => $stagingPath]);
            } else {
                $message = __('usageStats.compileMetrics.error', ['filename' => $filename]);
            }

            throw new JobException($message);
        }

        $temporaryTotalsDao = DAORegistry::getDAO('TemporaryTotalsDAO'); /** @var TemporaryTotalsDAO $temporaryTotalsDao */
        $temporaryItemInvestigationsDao = DAORegistry::getDAO('TemporaryItemInvestigationsDAO'); /** @var TemporaryItemInvestigationsDAO $temporaryItemInvestigationsDao */
        $temporaryItemRequestsDao = DAORegistry::getDAO('TemporaryItemRequestsDAO'); /** @var TemporaryItemRequestsDAO $temporaryItemRequestsDao */
        $temporaryTitleInvestigationsDao = DAORegistry::getDAO('TemporaryTitleInvestigationsDAO'); /** @var TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao */
        $temporaryTitleRequestsDao = DAORegistry::getDAO('TemporaryTitleRequestsDAO'); /** @var TemporaryTitleRequestsDAO $temporaryTitleRequestsDao */
        $temporaryInstitutionDao = DAORegistry::getDAO('TemporaryInstitutionsDAO'); /** @var TemporaryInstitutionsDAO $temporaryInstitutionDao */

        $temporaryTotalsDao->deleteByLoadId($this->loadId);
        $temporaryItemInvestigationsDao->deleteByLoadId($this->loadId);
        $temporaryItemRequestsDao->deleteByLoadId($this->loadId);
        $temporaryTitleInvestigationsDao->deleteByLoadId($this->loadId);
        $temporaryTitleRequestsDao->deleteByLoadId($this->loadId);
        $temporaryInstitutionDao->deleteByLoadId($this->loadId);
    }

    /**
     * Load the entries inside the temporary database associated with
     * the passed load id to the metrics tables.
     */
    protected function compileMetrics(): bool
    {
        $temporaryTotalsDao = DAORegistry::getDAO('TemporaryTotalsDAO'); /** @var TemporaryTotalsDAO $temporaryTotalsDao */
        $temporaryItemInvestigationsDao = DAORegistry::getDAO('TemporaryItemInvestigationsDAO'); /** @var TemporaryItemInvestigationsDAO $temporaryItemInvestigationsDao */
        $temporaryItemRequestsDao = DAORegistry::getDAO('TemporaryItemRequestsDAO'); /** @var TemporaryItemRequestsDAO $temporaryItemRequestsDao */
        $temporaryTitleInvestigationsDao = DAORegistry::getDAO('TemporaryTitleInvestigationsDAO'); /** @var TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao */
        $temporaryTitleRequestsDao = DAORegistry::getDAO('TemporaryTitleRequestsDAO'); /** @var TemporaryTitleRequestsDAO $temporaryTitleRequestsDao */

        $temporaryTotalsDao->removeDoubleClicks($this->loadId, StatisticsHelper::COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS);
        $temporaryItemInvestigationsDao->compileBookItemUniqueClicks($this->loadId);
        $temporaryItemInvestigationsDao->compileChapterItemUniqueClicks($this->loadId);
        $temporaryItemRequestsDao->compileBookItemUniqueClicks($this->loadId);
        $temporaryItemRequestsDao->compileChapterItemUniqueClicks($this->loadId);
        $temporaryTitleInvestigationsDao->compileTitleUniqueClicks($this->loadId);
        $temporaryTitleRequestsDao->compileTitleUniqueClicks($this->loadId);

        $temporaryTotalsDao->compileContextMetrics($this->loadId);
        $temporaryTotalsDao->compileSeriesMetrics($this->loadId);
        $temporaryTotalsDao->compileSubmissionMetrics($this->loadId);

        // Geo database only contains total and unique investigations (no extra requests differentiation)
        $temporaryTotalsDao->deleteSubmissionGeoDailyByLoadId($this->loadId); // always call first, before loading the data
        $temporaryTotalsDao->compileSubmissionGeoDailyMetrics($this->loadId);
        $temporaryTitleInvestigationsDao->compileSubmissionGeoDailyMetrics($this->loadId);

        // metrics_counter_submission_daily
        $temporaryTotalsDao->deleteCounterSubmissionDailyByLoadId($this->loadId); // always call first, before loading the data
        $temporaryTotalsDao->compileCounterSubmissionDailyMetrics($this->loadId);
        $temporaryItemInvestigationsDao->compileCounterSubmissionDailyMetrics($this->loadId);
        $temporaryItemRequestsDao->compileCounterSubmissionDailyMetrics($this->loadId);
        $temporaryTitleInvestigationsDao->compileCounterSubmissionDailyMetrics($this->loadId);
        $temporaryTitleRequestsDao->compileCounterSubmissionDailyMetrics($this->loadId);

        // metrics_counter_submission_institution_daily
        $temporaryTotalsDao->deleteCounterSubmissionInstitutionDailyByLoadId($this->loadId); // always call first, before loading the data
        $temporaryTotalsDao->compileCounterSubmissionInstitutionDailyMetrics($this->loadId);
        $temporaryItemInvestigationsDao->compileCounterSubmissionInstitutionDailyMetrics($this->loadId);
        $temporaryItemRequestsDao->compileCounterSubmissionInstitutionDailyMetrics($this->loadId);
        $temporaryTitleInvestigationsDao->compileCounterSubmissionInstitutionDailyMetrics($this->loadId);
        $temporaryTitleRequestsDao->compileCounterSubmissionInstitutionDailyMetrics($this->loadId);

        return true;
    }
}
