<?php
/**
 * @file classes/decision/types/AcceptFromInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AcceptFromInternal
 *
 * @brief A decision to accept the submission from the internal review stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\Accept;

class AcceptFromInternal extends Accept
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::ACCEPT_INTERNAL;
    }
}
