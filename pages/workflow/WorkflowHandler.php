<?php

/**
 * @file pages/workflow/WorkflowHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 *
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the submission workflow.
 */

namespace APP\pages\workflow;

use APP\components\listPanels\ContributorsListPanel;
use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\publication\Publication;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\components\forms\publication\TitleAbstractForm;
use PKP\context\Context;
use PKP\core\PKPApplication;
use PKP\notification\Notification;
use PKP\pages\workflow\PKPWorkflowHandler;
use PKP\security\Role;

class WorkflowHandler extends PKPWorkflowHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRoleAssignment(
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_ASSISTANT],
            [
                'access', 'index', 'submission',
                'internalReview', // Internal review
                'externalReview', // External review
                'editorial',
                'production',
            ]
        );
    }


    //
    // Public handler methods
    //
    /**
     * Show the internal review stage.
     *
     * @param array $args
     * @param Request $request
     */
    public function internalReview($args, $request)
    {
        $this->_redirectToIndex($args, $request);
    }

    /**
     * Setup variables for the template
     *
     * @param Request $request
     */
    public function setupIndex($request)
    {
        parent::setupIndex($request);

        $templateMgr = TemplateManager::getManager($request);
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        $submissionContext = $request->getContext();
        if ($submission->getData('contextId') !== $submissionContext->getId()) {
            $submissionContext = app()->get('context')->get($submission->getData('contextId'));
        }

        $latestPublication = $submission->getLatestPublication();

        $submissionLocale = $submission->getData('locale');
        $locales = collect($submissionContext->getSupportedSubmissionMetadataLocaleNames() + $submission->getPublicationLanguageNames())
            ->map(fn (string $name, string $locale) => ['key' => $locale, 'label' => $name])
            ->sortBy('key')
            ->values()
            ->toArray();

        $submissionApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $submissionContext->getData('urlPath'), 'submissions/' . $submission->getId());
        $latestPublicationApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $submissionContext->getData('urlPath'), 'submissions/' . $submission->getId() . '/publications/' . $latestPublication->getId());
        $temporaryFileApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $submissionContext->getData('urlPath'), 'temporaryFiles');

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

        $publicFileManager = new PublicFileManager();
        $baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($submissionContext->getId());

        $audienceForm = new \APP\components\forms\submission\AudienceForm($submissionApiUrl, $submission);
        $catalogEntryForm = new \APP\components\forms\publication\CatalogEntryForm($latestPublicationApiUrl, $locales, $latestPublication, $submission, $baseUrl, $temporaryFileApiUrl);
        $publicationDatesForm = new \APP\components\forms\submission\PublicationDatesForm($submissionApiUrl, $submission);


        $authorUserGroups = Repo::userGroup()->getByRoleIds([Role::ROLE_ID_AUTHOR], $submission->getData('contextId'));
        $publicationLicenseForm = new \APP\components\forms\publication\PublicationLicenseForm($latestPublicationApiUrl, $locales, $latestPublication, $submissionContext, $authorUserGroups);

        $templateMgr->setConstants([
            'FORM_AUDIENCE' => $audienceForm::FORM_AUDIENCE,
            'FORM_CATALOG_ENTRY' => $catalogEntryForm::FORM_CATALOG_ENTRY,
            'WORK_TYPE_AUTHORED_WORK' => Submission::WORK_TYPE_AUTHORED_WORK,
            'WORK_TYPE_EDITED_VOLUME' => Submission::WORK_TYPE_EDITED_VOLUME,
        ]);

        $components = $templateMgr->getState('components');
        $components[$audienceForm::FORM_AUDIENCE] = $audienceForm->getConfig();
        $components[$catalogEntryForm::FORM_CATALOG_ENTRY] = $this->getLocalizedForm($catalogEntryForm, $submissionLocale, $locales);
        $components[$publicationDatesForm::FORM_PUBLICATION_DATES] = $publicationDatesForm->getConfig();
        $components[$publicationLicenseForm->id] = $this->getLocalizedForm($publicationLicenseForm, $submissionLocale, $locales);

        $publicationFormIds = $templateMgr->getState('publicationFormIds');
        $publicationFormIds[] = $catalogEntryForm::FORM_CATALOG_ENTRY;

        $templateMgr->setState([
            'components' => $components,
            'chaptersGridUrl' => $chaptersGridUrl,
            'publicationFormIds' => $publicationFormIds,
            'editedVolumeLabel' => __('submission.workflowType.editedVolume.label'),
            'monographLabel' => __('common.publication'),
        ]);

        $templateMgr->assign([
            'pageComponent' => 'WorkflowPage',
        ]);
    }


    //
    // Protected helper methods
    //
    /**
     * Return the editor assignment notification type based on stage id.
     *
     * @param int $stageId
     *
     * @return ?int
     */
    protected function getEditorAssignmentNotificationTypeByStageId($stageId)
    {
        switch ($stageId) {
            case WORKFLOW_STAGE_ID_SUBMISSION:
                return Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
            case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
                return Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
            case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
                return Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
            case WORKFLOW_STAGE_ID_EDITING:
                return Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
            case WORKFLOW_STAGE_ID_PRODUCTION:
                return Notification::NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
        }
        return null;
    }

    protected function getTitleAbstractForm(string $latestPublicationApiUrl, array $locales, Publication $latestPublication, Context $context): TitleAbstractForm
    {
        return new TitleAbstractForm(
            $latestPublicationApiUrl,
            $locales,
            $latestPublication
        );
    }

    protected function getContributorsListPanel(Submission $submission, Context $context, array $locales, array $authorItems, bool $canEditPublication): ContributorsListPanel
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
