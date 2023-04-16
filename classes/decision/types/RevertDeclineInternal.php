<?php
/**
 * @file classes/decision/types/RevertDeclineInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RevertDeclineInternal
 *
 * @brief A decision to decline the submission from the internal review stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\RevertDecline;

class RevertDeclineInternal extends RevertDecline
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::REVERT_INTERNAL_DECLINE;
    }
}
