<?php

/**
 * @file classes/log/SubmissionEventLogEntry.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEventLogEntry
 * @ingroup log
 * @see SubmissionEventLogDAO
 *
 * @brief Describes an entry in the submission history log.
 */

import('lib.pkp.classes.log.PKPSubmissionEventLogEntry');

/**
 * Log entry event types. All types must be defined here.
 */
// General events					0x10000000
define('SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH',	0x10000008);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH',	0x10000009);
define('SUBMISSION_LOG_CATALOG_METADATA_UPDATE',	0x10000010);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_METADATA_UPDATE',	0x10000011);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE',	0x10000012);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE',	0x10000013);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE',	0x10000014);
define('SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE',	0x10000015);

class SubmissionEventLogEntry extends PKPSubmissionEventLogEntry {
}

