<?php
/**
 * @file classes/decision/types/SkipInternalReview.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SkipInternalReview
 *
 * @brief A decision to send a submission to the external review round from the submission stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use PKP\decision\types\SendExternalReview;

class SkipInternalReview extends SendExternalReview
{
    public function getDecision(): int
    {
        return Decision::SKIP_INTERNAL_REVIEW;
    }
}
