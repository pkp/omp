<?php

/**
 * @file pages/reviewer/ReviewerHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewerHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for reviewer functions.
 */

import('lib.pkp.pages.reviewer.PKPReviewerHandler');

use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\Role;

class ReviewerHandler extends PKPReviewerHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            Role::ROLE_ID_REVIEWER,
            [
                'submission', 'step', 'saveStep',
                'showDeclineReview', 'saveDeclineReview', 'downloadFile'
            ]
        );
    }

    /**
     * @see PKPHandler::authorize()
     *
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $router = $request->getRouter();
        $this->addPolicy(new SubmissionAccessPolicy(
            $request,
            $args,
            $roleAssignments
        ));
        return parent::authorize($request, $args, $roleAssignments);
    }
}
