<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the submssion workflow.
 */

import('lib.pkp.pages.workflow.PKPWorkflowHandler');

use APP\file\PublicFileManager;

use APP\submission\Submission;
use APP\template\TemplateManager;
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
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_ASSISTANT],
            [
                'access', 'index', 'submission',
                'editorDecisionActions', // Submission & review
                'internalReview', // Internal review
                'externalReview', // External review
                'editorial',
                'production',
                'submissionHeader',
                'submissionProgressBar',
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
     * @param PKPRequest $request
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
        $submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

        $submissionContext = $request->getContext();
        if ($submission->getContextId() !== $submissionContext->getId()) {
            $submissionContext = Services::get('context')->get($submission->getContextId());
        }

        $supportedFormLocales = $submissionContext->getSupportedFormLocales();
        $localeNames = AppLocale::getAllLocales();
        $locales = array_map(function ($localeKey) use ($localeNames) {
            return ['key' => $localeKey, 'label' => $localeNames[$localeKey]];
        }, $supportedFormLocales);

        $latestPublication = $submission->getLatestPublication();

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

        $audienceForm = new APP\components\forms\submission\AudienceForm($submissionApiUrl, $submission);
        $catalogEntryForm = new APP\components\forms\publication\CatalogEntryForm($latestPublicationApiUrl, $locales, $latestPublication, $submission, $baseUrl, $temporaryFileApiUrl);
        $publicationDatesForm = new APP\components\forms\submission\PublicationDatesForm($submissionApiUrl, $submission);

        $templateMgr->setConstants([
            'FORM_AUDIENCE' => FORM_AUDIENCE,
            'FORM_CATALOG_ENTRY' => FORM_CATALOG_ENTRY,
            'WORK_TYPE_AUTHORED_WORK' => Submission::WORK_TYPE_AUTHORED_WORK,
            'WORK_TYPE_EDITED_VOLUME' => Submission::WORK_TYPE_EDITED_VOLUME,
        ]);

        $components = $templateMgr->getState('components');
        $components[FORM_AUDIENCE] = $audienceForm->getConfig();
        $components[FORM_CATALOG_ENTRY] = $catalogEntryForm->getConfig();
        $components[FORM_PUBLICATION_DATES] = $publicationDatesForm->getConfig();

        $publicationFormIds = $templateMgr->getState('publicationFormIds');
        $publicationFormIds[] = FORM_CATALOG_ENTRY;

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
     * @return int
     */
    protected function getEditorAssignmentNotificationTypeByStageId($stageId)
    {
        switch ($stageId) {
            case WORKFLOW_STAGE_ID_SUBMISSION:
                return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
            case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
                return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
            case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
                return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
            case WORKFLOW_STAGE_ID_EDITING:
                return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
            case WORKFLOW_STAGE_ID_PRODUCTION:
                return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
        }
        return null;
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
