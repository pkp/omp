<?php

/**
 * @file jobs/statistics/CompileUniqueRequests.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CompileUniqueRequests
 *
 * @ingroup jobs
 *
 * @brief Compile unique requests according to COUNTER guidelines.
 */

namespace APP\jobs\statistics;

use APP\statistics\TemporaryItemRequestsDAO;
use APP\statistics\TemporaryTitleRequestsDAO;
use PKP\db\DAORegistry;
use PKP\jobs\BaseJob;

class CompileUniqueRequests extends BaseJob
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
        $temporaryItemRequestsDao = DAORegistry::getDAO('TemporaryItemRequestsDAO'); /** @var TemporaryItemRequestsDAO $temporaryItemRequestsDao */
        $temporaryTitleRequestsDao = DAORegistry::getDAO('TemporaryTitleRequestsDAO'); /** @var TemporaryTitleRequestsDAO $temporaryTitleRequestsDao */
        $temporaryItemRequestsDao->compileBookItemUniqueClicks($this->loadId);
        $temporaryItemRequestsDao->compileChapterItemUniqueClicks($this->loadId);
        $temporaryTitleRequestsDao->compileTitleUniqueClicks($this->loadId);
    }
}
