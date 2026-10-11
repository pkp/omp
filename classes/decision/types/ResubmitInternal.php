<?php

/**
 * @file classes/decision/types/ResubmitInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ResubmitInternal
 *
 * @brief A decision to request revisions during an internal review round
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\Resubmit;

class ResubmitInternal extends Resubmit
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::RESUBMIT_INTERNAL;
    }
}
