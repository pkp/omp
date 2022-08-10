<?php

/**
 * @defgroup decision Decision
 */

/**
 * @file classes/decision/Decision.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Decision
 * @ingroup decision
 *
 * @see DAO
 *
 * @brief An editorial decision taken on a submission, such as to accept, decline or request revisions.
 */

namespace APP\decision;

use PKP\decision\Decision as BaseDecision;

class Decision extends BaseDecision
{
    public const INTERNAL_REVIEW = 1;
    public const ACCEPT = 2;
    public const EXTERNAL_REVIEW = 3;
    public const PENDING_REVISIONS = 4;
    public const RESUBMIT = 5;
    public const DECLINE = 6;
    public const RECOMMEND_EXTERNAL_REVIEW = 15;
    public const REVERT_INITIAL_DECLINE = 18;
    public const SKIP_INTERNAL_REVIEW = 20;
    public const ACCEPT_INTERNAL = 21;
    public const PENDING_REVISIONS_INTERNAL = 22;
    public const RESUBMIT_INTERNAL = 23;
    public const DECLINE_INTERNAL = 24;
    public const RECOMMEND_ACCEPT_INTERNAL = 25;
    public const RECOMMEND_PENDING_REVISIONS_INTERNAL = 26;
    public const RECOMMEND_RESUBMIT_INTERNAL = 27;
    public const RECOMMEND_DECLINE_INTERNAL = 28;
    public const REVERT_INTERNAL_DECLINE = 29;
    public const NEW_INTERNAL_ROUND = 30;
    public const CANCEL_INTERNAL_REVIEW_ROUND = 34;
}

if (!PKP_STRICT_MODE) {
    define('SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW', Decision::INTERNAL_REVIEW);
    define('SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW', Decision::EXTERNAL_REVIEW);
    define('SUBMISSION_EDITOR_DECISION_ACCEPT', Decision::ACCEPT);
    define('SUBMISSION_EDITOR_DECISION_DECLINE', Decision::DECLINE);
    define('SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS', Decision::PENDING_REVISIONS);
    define('SUBMISSION_EDITOR_DECISION_RESUBMIT', Decision::RESUBMIT);
    define('SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW', Decision::RECOMMEND_EXTERNAL_REVIEW);
}
