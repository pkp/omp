<?php

/**
 * @file jobs/statistics/CompileSubmissionGeoDailyMetrics.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CompileSubmissionGeoDailyMetrics
 *
 * @ingroup jobs
 *
 * @brief Compile submission Geo daily metrics.
 */

namespace APP\jobs\statistics;

use APP\statistics\TemporaryTitleInvestigationsDAO;
use APP\statistics\TemporaryTotalsDAO;
use PKP\db\DAORegistry;
use PKP\jobs\BaseJob;

class CompileSubmissionGeoDailyMetrics extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * The load ID = usage stats log file name
     */
    protected string $loadId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $loadId)
    {
        parent::__construct();
        $this->loadId = $loadId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $temporaryTotalsDao = DAORegistry::getDAO('TemporaryTotalsDAO'); /** @var TemporaryTotalsDAO $temporaryTotalsDao */
        $temporaryTitleInvestigationsDao = DAORegistry::getDAO('TemporaryTitleInvestigationsDAO'); /** @var TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao */

        // Geo database only contains total and unique investigations (no extra requests differentiation)
        $temporaryTotalsDao->deleteSubmissionGeoDailyByLoadId($this->loadId); // always call first, before loading the data
        $temporaryTotalsDao->compileSubmissionGeoDailyMetrics($this->loadId);
        $temporaryTitleInvestigationsDao->compileSubmissionGeoDailyMetrics($this->loadId);
    }
}
