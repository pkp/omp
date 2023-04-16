<?php

/**
 * @file classes/log/MonographFileEmailLogEntry.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEmailLogEntry
 *
 * @ingroup log
 *
 * @see MonographFileEmailLogDAO
 *
 * @brief Describes an entry in the monograph file email log.
 */

namespace APP\log;

use PKP\log\EmailLogEntry;

class MonographFileEmailLogEntry extends EmailLogEntry
{
    public function setFileId($fileId)
    {
        return $this->setAssocId($fileId);
    }

    public function getFileId()
    {
        return $this->getAssocId();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\log\MonographFileEmailLogEntry', '\MonographFileEmailLogEntry');
}
