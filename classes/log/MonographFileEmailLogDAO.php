<?php

/**
 * @file classes/log/MonographFileEmailLogDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEmailLogDAO
 * @ingroup log
 *
 * @see EmailLogDAO
 *
 * @brief Extension to EmailLogDAO for monograph file specific log entries.
 */

namespace APP\log;

use APP\core\Application;
use PKP\log\EmailLogDAO;

class MonographFileEmailLogDAO extends EmailLogDAO
{
    /**
     * Instantiate and return a MonographFileEmailLogEntry.
     *
     * @return MonographFileEmailLogEntry
     */
    public function newDataObject()
    {
        $returner = new MonographFileEmailLogEntry();
        $returner->setAssocType(Application::ASSOC_TYPE_SUBMISSION_FILE);
        return $returner;
    }

    /**
     * Get monograph file email log entries by file ID and event type.
     *
     * @param int $fileId
     * @param int $eventType SUBMISSION_EMAIL_...
     * @param int $userId optional Return only emails sent to this user.
     *
     * @return DAOResultFactory
     */
    public function getByEventType($fileId, $eventType, $userId = null)
    {
        return parent::_getByEventType(Application::ASSOC_TYPE_SUBMISSION_FILE, $fileId, $eventType, $userId);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\log\MonographFileEmailLogDAO', '\MonographFileEmailLogDAO');
}
