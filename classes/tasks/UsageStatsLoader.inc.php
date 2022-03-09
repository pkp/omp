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
use APP\statistics\UsageStatsTotalTemporaryRecordDAO;
use APP\statistics\UsageStatsUniqueItemInvestigationsTemporaryRecordDAO;
use APP\statistics\UsageStatsUniqueItemRequestsTemporaryRecordDAO;
use APP\statistics\UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO;
use APP\statistics\UsageStatsUniqueTitleRequestsTemporaryRecordDAO;
use PKP\db\DAORegistry;
use PKP\statistics\UsageStatsInstitutionTemporaryRecordDAO;
use PKP\task\PKPUsageStatsLoader;

class UsageStatsLoader extends PKPUsageStatsLoader
{
    private UsageStatsInstitutionTemporaryRecordDAO $statsInstitutionDao;
    private UsageStatsTotalTemporaryRecordDAO $statsTotalDao;
    private UsageStatsUniqueItemInvestigationsTemporaryRecordDAO $statsUniqueItemInvestigationsDao;
    private UsageStatsUniqueItemRequestsTemporaryRecordDAO $statsUniqueItemRequestsDao;
    private UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO $statsUniqueTitleInvestigationsDao;
    private UsageStatsUniqueTitleRequestsTemporaryRecordDAO $statsUniqueTitleRequestsDao;

    /**
     * Constructor.
     */
    public function __construct($args)
    {
        $this->statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var UsageStatsInstitutionTemporaryRecordDAO $statsInstitutionDao */
        $this->statsTotalDao = DAORegistry::getDAO('UsageStatsTotalTemporaryRecordDAO'); /* @var UsageStatsTotalTemporaryRecordDAO $statsTotalDao */
        $this->statsUniqueItemInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueItemInvestigationsTemporaryRecordDAO'); /* @var UsageStatsUniqueItemInvestigationsTemporaryRecordDAO $statsUniqueItemInvestigationsDao */
        $this->statsUniqueItemRequestsDao = DAORegistry::getDAO('UsageStatsUniqueItemRequestsTemporaryRecordDAO'); /* @var UsageStatsUniqueItemRequestsTemporaryRecordDAO $statsUniqueItemRequestsDao */
        $this->statsUniqueTitleInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO'); /* @var UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO $statsUniqueTitleInvestigationsDao */
        $this->statsUniqueTitleRequestsDao = DAORegistry::getDAO('UsageStatsUniqueTitleRequestsTemporaryRecordDAO'); /* @var UsageStatsUniqueTitleRequestsTemporaryRecordDAO $statsUniqueTitleRequestsDao */
        parent::__construct($args);
    }

    /**
     * @copydoc PKPUsageStatsLoader::deleteByLoadId()
     */
    protected function deleteByLoadId(string $loadId): void
    {
        $this->statsInstitutionDao->deleteByLoadId($loadId);
        $this->statsTotalDao->deleteByLoadId($loadId);
        $this->statsUniqueItemInvestigationsDao->deleteByLoadId($loadId);
        $this->statsUniqueItemRequestsDao->deleteByLoadId($loadId);
        $this->statsUniqueTitleInvestigationsDao->deleteByLoadId($loadId);
        $this->statsUniqueTitleRequestsDao->deleteByLoadId($loadId);
    }

    /**
     * @copydoc PKPUsageStatsLoader::insertTemporaryUsageStatsData()
     */
    protected function insertTemporaryUsageStatsData(object $entry, int $lineNumber, string $loadId): void
    {
        $this->statsInstitutionDao->insert($entry->institutionIds, $lineNumber, $loadId);
        $this->statsTotalDao->insert($entry, $lineNumber, $loadId);
        if (!empty($entry->submissionId)) {
            $this->statsUniqueItemInvestigationsDao->insert($entry, $lineNumber, $loadId);
            $this->statsUniqueTitleInvestigationsDao->insert($entry, $lineNumber, $loadId);
            if ($entry->assocType == Application::ASSOC_TYPE_SUBMISSION_FILE) {
                $this->statsUniqueItemRequestsDao->insert($entry, $lineNumber, $loadId);
                $this->statsUniqueTitleRequestsDao->insert($entry, $lineNumber, $loadId);
            }
        }
    }

    /**
     * @copydoc PKPUsageStatsLoader::checkForeignKeys()
     */
    protected function checkForeignKeys(object $entry): array
    {
        return $this->statsTotalDao->checkForeignKeys($entry);
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
        if (!empty($entry->chapterId)) {
            if (!is_int($entry->chapterId)) {
                throw new \Exception(__('admin.scheduledTask.usageStatsLoader.invalidLogEntry.chapterId'));
            }
        }
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\tasks\UsageStatsLoader', '\UsageStatsLoader');
}
