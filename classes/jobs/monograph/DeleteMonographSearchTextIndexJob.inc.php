<?php

declare(strict_types=1);

/**
 * @file classes/jobs/monograph/Metadata/DeleteMonographSearchTextIndexJob.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DeleteMonographSearchTextIndexJob
 * @ingroup jobs
 *
 * @brief Class to handle the Monograph Deleting job
 */

namespace APP\jobs\monograph;

use APP\core\Application;
use PKP\Support\Jobs\BaseJob;

class DeleteMonographSearchTextIndexJob extends BaseJob
{
    /**
     * @var int $monographId The Monograph ID
     */
    protected $monographId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $monographId)
    {
        parent::__construct();

        $this->monographId = $monographId;
    }

    /**
     * Execute the job.
     *
     */
    public function handle(): void
    {
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->deleteTextIndex($this->monographId);
        $monographSearchIndex->submissionChangesFinished();
    }
}
