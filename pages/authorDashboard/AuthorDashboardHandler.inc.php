<?php

/**
 * @file pages/authorDashboard/AuthorDashboardHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardHandler
 * @ingroup pages_authorDashboard
 *
 * @brief Handle requests for the author dashboard.
 */

use APP\template\TemplateManager;

use PKP\submissionFile\SubmissionFile;

// Import base class
import('lib.pkp.pages.authorDashboard.PKPAuthorDashboardHandler');

class AuthorDashboardHandler extends PKPAuthorDashboardHandler
{
    //
    // Public handler operations
    //
    /**
     * Displays the author dashboard.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function submission($args, $request)
    {
        $submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
        $templateMgr = TemplateManager::getManager($request);
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /** @var ReviewRoundDAO $reviewRoundDao */
        $internalReviewRounds = $reviewRoundDao->getBySubmissionId($submission->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
        $templateMgr->assign('internalReviewRounds', $internalReviewRounds);
        return parent::submission($args, $request);
    }

    /**
     * @copydoc PKPAuthorDashboardHandler::setupTemplate()
     */
    public function setupTemplate($request)
    {
        parent::setupTemplate($request);

        $submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

        $chaptersGridUrl = $request->getDispatcher()->url(
            $request,
            PKPApplication::ROUTE_COMPONENT,
            null,
            'grid.users.chapter.ChapterGridHandler',
            'fetchGrid',
            null,
            [
                'submissionId' => $submission->getId(),
                'publicationId' => '__publicationId__',
            ]
        );

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setState([
            'chaptersGridUrl' => $chaptersGridUrl,
        ]);
    }


    //
    // Protected helper methods
    //
    /**
     * Get the SubmissionFile::SUBMISSION_FILE_... file stage based on the current
     * WORKFLOW_STAGE_... workflow stage.
     *
     * @param int $currentStage WORKFLOW_STAGE_...
     *
     * @return int SubmissionFile::SUBMISSION_FILE_...
     */
    protected function _fileStageFromWorkflowStage($currentStage)
    {
        switch ($currentStage) {
            case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
                return SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION;
            default:
                return parent::_fileStageFromWorkflowStage($currentStage);
        }
    }

    /**
     * Get the notification request options.
     *
     * @param Submission $submission
     *
     * @return array
     */
    protected function _getNotificationRequestOptions($submission)
    {
        $submissionAssocTypeAndIdArray = [ASSOC_TYPE_SUBMISSION, $submission->getId()];
        $notificationRequestOptions = parent::_getNotificationRequestOptions($submission);
        $notificationRequestOptions[NOTIFICATION_LEVEL_TASK][NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS] = $submissionAssocTypeAndIdArray;
        $notificationRequestOptions[NOTIFICATION_LEVEL_NORMAL][NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW] = $submissionAssocTypeAndIdArray;
        return $notificationRequestOptions;
    }

    /**
     * @copydoc PKPWorkflowHandler::_getRepresentationsGridUrl()
     */
    protected function _getRepresentationsGridUrl($request, $submission)
    {
        return $request->getDispatcher()->url(
            $request,
            PKPApplication::ROUTE_COMPONENT,
            null,
            'grid.catalogEntry.PublicationFormatGridHandler',
            'fetchGrid',
            null,
            [
                'submissionId' => $submission->getId(),
                'publicationId' => '__publicationId__',
            ]
        );
    }
}
