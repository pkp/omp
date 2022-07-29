<?php

/**
 * @file controllers/tab/workflow/ReviewRoundTabHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundTabHandler
 * @ingroup controllers_tab_workflow
 *
 * @brief Handle AJAX operations for review round tabs on review stages workflow pages.
 */

namespace APP\controllers\tab\workflow;

use APP\handler\Handler;
use PKP\controllers\tab\workflow\PKPReviewRoundTabHandler;
use PKP\security\authorization\WorkflowStageAccessPolicy;

use PKP\security\Role;

class ReviewRoundTabHandler extends PKPReviewRoundTabHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_ASSISTANT],
            ['internalReviewRound', 'externalReviewRound']
        );
    }


    //
    // Extended methods from Handler
    //
    /**
     * @see PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $stageId = (int) $request->getUserVar('stageId'); // This is validated in WorkflowStageAccessPolicy.

        $this->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * JSON fetch the internal review round info (tab).
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function internalReviewRound($args, $request)
    {
        return $this->_reviewRound($args, $request);
    }
}
