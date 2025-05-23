<?php

/**
 * @file jobs/statistics/CompileUniqueInvestigations.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CompileUniqueInvestigations
 *
 * @ingroup jobs
 *
 * @brief Remove unique investigations according to COUNTER guidelines.
 */

namespace APP\jobs\statistics;

use APP\statistics\TemporaryItemInvestigationsDAO;
use APP\statistics\TemporaryTitleInvestigationsDAO;
use PKP\db\DAORegistry;
use PKP\jobs\BaseJob;

class CompileUniqueInvestigations extends BaseJob
{
    public int $timeout = 600;

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
        $temporaryItemInvestigationsDao = DAORegistry::getDAO('TemporaryItemInvestigationsDAO'); /** @var TemporaryItemInvestigationsDAO $temporaryItemInvestigationsDao */
        $temporaryTitleInvestigationsDao = DAORegistry::getDAO('TemporaryTitleInvestigationsDAO'); /** @var TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao */
        $temporaryItemInvestigationsDao->compileBookItemUniqueClicks($this->loadId);
        $temporaryItemInvestigationsDao->compileChapterItemUniqueClicks($this->loadId);
        $temporaryTitleInvestigationsDao->compileTitleUniqueClicks($this->loadId);
    }
}
