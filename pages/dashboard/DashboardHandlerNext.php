<?php
/**
 * @file pages/dashboard/DashboardHandlerNext.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DashboardHandlerNext
 *
 * @ingroup pages_dashboard
 *
 * @brief Handle requests for user's dashboard.
 */

namespace APP\pages\dashboard;

use APP\components\forms\dashboard\SubmissionFilters;
use APP\core\Request;
use APP\facades\Repo;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\decision\Decision;
use PKP\pages\dashboard\PKPDashboardHandlerNext;
use PKP\submissionFile\SubmissionFile;

class DashboardHandlerNext extends PKPDashboardHandlerNext
{
    /**
     * Setup variables for the template
     *
     * @param Request $request
     */
    public function setupIndex($request)
    {
        parent::setupIndex($request);

        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign([
            'pageComponent' => 'Page',
        ]);

        $templateMgr->setConstants([
            'DECISION_INTERNAL_REVIEW' => Decision::INTERNAL_REVIEW,
            'DECISION_RECOMMEND_EXTERNAL_REVIEW' => Decision::RECOMMEND_EXTERNAL_REVIEW,
            'DECISION_SKIP_INTERNAL_REVIEW' => Decision::SKIP_INTERNAL_REVIEW,
            'DECISION_ACCEPT_INTERNAL' => Decision::ACCEPT_INTERNAL,
            'DECISION_PENDING_REVISIONS_INTERNAL' => Decision::PENDING_REVISIONS_INTERNAL,
            'DECISION_RESUBMIT_INTERNAL' => Decision::RESUBMIT_INTERNAL,
            'DECISION_DECLINE_INTERNAL' => Decision::DECLINE_INTERNAL,
            'DECISION_RECOMMEND_ACCEPT_INTERNAL' => Decision::RECOMMEND_ACCEPT_INTERNAL,
            'DECISION_RECOMMEND_PENDING_REVISIONS_INTERNAL' => Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL,
            'DECISION_RECOMMEND_RESUBMIT_INTERNAL' => Decision::RECOMMEND_RESUBMIT_INTERNAL,
            'DECISION_RECOMMEND_DECLINE_INTERNAL' => Decision::RECOMMEND_DECLINE_INTERNAL,
            'DECISION_REVERT_INTERNAL_DECLINE' => Decision::REVERT_INTERNAL_DECLINE,
            'DECISION_NEW_INTERNAL_ROUND' => Decision::NEW_INTERNAL_ROUND,
            'DECISION_CANCEL_INTERNAL_REVIEW_ROUND' => Decision::CANCEL_INTERNAL_REVIEW_ROUND,

            'SUBMISSION_FILE_INTERNAL_REVIEW_FILE' => SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
            'SUBMISSION_FILE_INTERNAL_REVIEW_REVISION' => SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,

            'WORK_TYPE_AUTHORED_WORK' => Submission::WORK_TYPE_AUTHORED_WORK,
            'WORK_TYPE_EDITED_VOLUME' => Submission::WORK_TYPE_EDITED_VOLUME,

        ]);
    }


    protected function getSubmissionFiltersForm($userRoles, $context)
    {
        $categories = Repo::category()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany();

        return new SubmissionFilters(
            $context,
            $userRoles,
            $categories
        );
    }


}
