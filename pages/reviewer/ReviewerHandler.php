<?php

/**
 * @file pages/reviewer/ReviewerHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewerHandler
 *
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for reviewer functions.
 */

namespace APP\pages\reviewer;

use APP\core\Request;
use APP\facades\Repo;
use PKP\invitation\core\enums\InvitationAction;
use PKP\pages\reviewer\PKPReviewerHandler;
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
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $context = $request->getContext();
        if ($context->getData('reviewerAccessKeysEnabled')) {
            $accessKeyCode = $request->getUserVar('key');
            if ($accessKeyCode) {
                $invitation = Repo::invitation()->getByKey($accessKeyCode);

                if (isset($invitation)) {
                    $invitationHandler = $invitation->getInvitationActionRedirectController();
                    $invitationHandler->preRedirectActions(InvitationAction::ACCEPT);
                    $invitationHandler->acceptHandle($request);
                }
            }
        }

        $this->addPolicy(new SubmissionAccessPolicy(
            $request,
            $args,
            $roleAssignments
        ));

        return parent::authorize($request, $args, $roleAssignments);
    }
}
