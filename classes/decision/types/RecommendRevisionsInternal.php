<?php
/**
 * @file classes/decision/types/RecommendRevisionsInternal.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RecommendRevisionsInternal
 *
 * @brief A recommendation to request revisions during in the internal review stage
 */

namespace APP\decision\types;

use APP\decision\Decision;
use APP\decision\types\traits\InInternalReviewRound;
use PKP\decision\types\RecommendRevisions;

class RecommendRevisionsInternal extends RecommendRevisions
{
    use InInternalReviewRound;

    public function getDecision(): int
    {
        return Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL;
    }
}
