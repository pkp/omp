<?php
/**
 * @file classes/decision/types/RecommendDeclineInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RecommendDeclineInternal
 *
 * @brief A recommendation to decline a submission in the internal review stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\RecommendDecline;

class RecommendDeclineInternal extends RecommendDecline
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::RECOMMEND_DECLINE_INTERNAL;
    }
}
