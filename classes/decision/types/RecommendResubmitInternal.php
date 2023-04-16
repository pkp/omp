<?php
/**
 * @file classes/decision/types/RecommendResubmitInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RecommendResubmitInternal
 *
 * @brief A recommendation to request revisions to be sent another round of review in the internal review stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\RecommendResubmit;

class RecommendResubmitInternal extends RecommendResubmit
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::RECOMMEND_RESUBMIT_INTERNAL;
    }
}
