<?php

/**
 * @file pages/authorDashboard/AuthorDashboardHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardHandler
 *
 * @ingroup pages_authorDashboard
 *
 * @brief Handle requests for the author dashboard.
 */

namespace APP\pages\authorDashboard;

use APP\components\listPanels\ContributorsListPanel;
use APP\core\Application;
use APP\core\Request;
use APP\publication\Publication;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\components\forms\publication\TitleAbstractForm;
use PKP\context\Context;
use PKP\core\PKPApplication;
use PKP\db\DAORegistry;
use PKP\notification\Notification;
use PKP\pages\authorDashboard\PKPAuthorDashboardHandler;
use PKP\submission\reviewRound\ReviewRoundDAO;
use PKP\submissionFile\SubmissionFile;

class AuthorDashboardHandler extends PKPAuthorDashboardHandler
{
    //
    // Public handler operations
    //
    /**
     * Displays the author dashboard.
     *
     * @param array $args
     * @param Request $request
     */
    public function submission($args, $request)
    {
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
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

        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

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
        $submissionAssocTypeAndIdArray = [Application::ASSOC_TYPE_SUBMISSION, $submission->getId()];
        $notificationRequestOptions = [];
        $notificationRequestOptions[Notification::NOTIFICATION_LEVEL_TASK][Notification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS] = $submissionAssocTypeAndIdArray;
        $notificationRequestOptions[Notification::NOTIFICATION_LEVEL_NORMAL][Notification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW] = $submissionAssocTypeAndIdArray;
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

    protected function getTitleAbstractForm(string $latestPublicationApiUrl, array $locales, Publication $latestPublication, Context $context): TitleAbstractForm
    {
        return new TitleAbstractForm(
            $latestPublicationApiUrl,
            $locales,
            $latestPublication
        );
    }

    protected function getContributorsListPanel(Submission $submission, Context $context, array $locales, array $authorItems, ?bool $canEditPublication): ContributorsListPanel
    {
        return new ContributorsListPanel(
            'contributors',
            __('publication.contributors'),
            $submission,
            $context,
            $locales,
            $authorItems,
            $canEditPublication
        );
    }
}
