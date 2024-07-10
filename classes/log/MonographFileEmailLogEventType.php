<?php

/**
 * @file classes/log/MonographFileEmailLogEventType.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @enum MonographFileEmailLogEventType
 *
 * @ingroup log
 *
 * @brief Enumeration for monograph file log event types.
 */

namespace APP\log;

use PKP\log\core\EmailLogEventType;

enum MonographFileEmailLogEventType: int implements EmailLogEventType
{
}
