<?php

/**
 * @file classes/log/SubmissionEventLogEntry.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEventLogEntry
 *
 * @ingroup log
 *
 * @see SubmissionEventLogDAO
 *
 * @brief Describes an entry in the submission history log.
 */

namespace APP\log;

use PKP\log\PKPSubmissionEventLogEntry;

/**
 * Log entry event types. All types must be defined here.
 */
// General events					0x10000000

class SubmissionEventLogEntry extends PKPSubmissionEventLogEntry
{
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH = 268435464;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH = 268435465;
    public const SUBMISSION_LOG_CATALOG_METADATA_UPDATE = 2268435472;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_METADATA_UPDATE = 68435473;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE = 268435474;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE = 268435475;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE = 268435476;
    public const SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE = 268435477;
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\log\SubmissionEventLogEntry', '\SubmissionEventLogEntry');
    foreach ([
        'SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH',
        'SUBMISSION_LOG_CATALOG_METADATA_UPDATE',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_METADATA_UPDATE',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE',
        'SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE',
    ] as $constantName) {
        define($constantName, constant('\SubmissionEventLogEntry::' . $constantName));
    }
}
