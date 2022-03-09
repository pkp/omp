<?php

/**
 * @file classes/tasks/UsageStatsLoader.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsLoader
 * @ingroup tasks
 *
 * @brief Scheduled task to extract transform and load usage statistics data into database.
 */

namespace APP\tasks;

use APP\core\Application;
use APP\statistics\TemporaryTitleInvestigationsDAO;
use APP\statistics\TemporaryTitleRequestsDAO;
use Exception;
use PKP\db\DAORegistry;
use PKP\scheduledTask\ScheduledTaskHelper;
use PKP\task\PKPUsageStatsLoader;

class UsageStatsLoader extends PKPUsageStatsLoader
{
    /** DAOs for temporary usage stats tables where the log entries are inserted for further processing */
    private TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao;
    private TemporaryTitleRequestsDAO $temporaryTitleRequestsDao;

    /**
     * Constructor.
     */
    public function __construct($args)
    {
        $this->temporaryTitleInvestigationsDao = DAORegistry::getDAO('TemporaryTitleInvestigationsDAO'); /* @var TemporaryTitleInvestigationsDAO $temporaryTitleInvestigationsDao */
        $this->temporaryTitleRequestsDao = DAORegistry::getDAO('TemporaryTitleRequestsDAO'); /* @var TemporaryTitleRequestsDAO $temporaryTitleRequestsDao */
        parent::__construct($args);
    }

    /**
     * @copydoc PKPUsageStatsLoader::deleteByLoadId()
     */
    protected function deleteByLoadId(string $loadId): void
    {
        parent::deleteByLoadId($loadId);
        $this->temporaryTitleInvestigationsDao->deleteByLoadId($loadId);
        $this->temporaryTitleRequestsDao->deleteByLoadId($loadId);
    }

    /**
     * @copydoc PKPUsageStatsLoader::insertTemporaryUsageStatsData()
     */
    protected function insertTemporaryUsageStatsData(object $entry, int $lineNumber, string $loadId): void
    {
        try {
            $this->temporaryTotalsDao->insert($entry, $lineNumber, $loadId);
            $this->temporaryInstitutionsDao->insert($entry->institutionIds, $lineNumber, $loadId);
            if (!empty($entry->submissionId)) {
                $this->temporaryItemInvestigationsDao->insert($entry, $lineNumber, $loadId);
                $this->temporaryTitleInvestigationsDao->insert($entry, $lineNumber, $loadId);
                if ($entry->assocType == Application::ASSOC_TYPE_SUBMISSION_FILE) {
                    $this->temporaryItemRequestsDao->insert($entry, $lineNumber, $loadId);
                    $this->temporaryTitleRequestsDao->insert($entry, $lineNumber, $loadId);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->addExecutionLogEntry(__('admin.scheduledTask.usageStatsLoader.insertError', ['file' => $loadId, 'lineNumber' => $lineNumber, 'msg' => $e->getMessage()]), ScheduledTaskHelper::SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
        }
    }

    /**
     * @copydoc PKPUsageStatsLoader::getValidAssocTypes()
     */
    protected function getValidAssocTypes(): array
    {
        return [
            Application::ASSOC_TYPE_SUBMISSION_FILE,
            Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER,
            Application::ASSOC_TYPE_CHAPTER,
            Application::ASSOC_TYPE_SUBMISSION,
            Application::ASSOC_TYPE_SERIES,
            Application::ASSOC_TYPE_PRESS,
        ];
    }

    /**
     * @copydoc PKPUsageStatsLoader::isLogEntryValid()
     */
    protected function isLogEntryValid(object $entry): void
    {
        parent::isLogEntryValid($entry);
        if (!empty($entry->chapterId) && !is_int($entry->chapterId)) {
            throw new Exception(__('admin.scheduledTask.usageStatsLoader.invalidLogEntry.chapterId'));
        }
        if (!empty($entry->seriesId) && !is_int($entry->seriesId)) {
            throw new Exception(__('admin.scheduledTask.usageStatsLoader.invalidLogEntry.seriesId'));
        }
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\tasks\UsageStatsLoader', '\UsageStatsLoader');
}
