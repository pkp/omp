<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the submssion workflow.
 */

import('lib.pkp.pages.workflow.PKPWorkflowHandler');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class WorkflowHandler extends PKPWorkflowHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
			array(
				'access', 'index', 'submission',
				'editorDecisionActions', // Submission & review
				'internalReview', // Internal review
				'externalReview', // External review
				'editorial',
				'production',
				'submissionHeader',
				'submissionProgressBar',
			)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the internal review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function internalReview($args, $request) {
		$this->_redirectToIndex($args, $request);
	}

	/**
	 * Setup variables for the template
	 * @param $request Request
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);

		$templateMgr = TemplateManager::getManager($request);
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		$submissionContext = $request->getContext();
		if ($submission->getContextId() !== $submissionContext->getId()) {
			$submissionContext = Services::get('context')->get($submission->getContextId());
		}

		$supportedFormLocales = $submissionContext->getSupportedFormLocales();
		$localeNames = AppLocale::getAllLocales();
		$locales = array_map(function($localeKey) use ($localeNames) {
			return ['key' => $localeKey, 'label' => $localeNames[$localeKey]];
		}, $supportedFormLocales);

		$latestPublication = $submission->getLatestPublication();

		$submissionApiUrl = $request->getDispatcher()->url($request, ROUTE_API, $submissionContext, 'submissions/' . $submission->getId());
		$latestPublicationApiUrl = $request->getDispatcher()->url($request, ROUTE_API, $submissionContext, 'submissions/' . $submission->getId() . '/publications/' . $latestPublication->getId());
		$temporaryFileApiUrl = $request->getDispatcher()->url($request, ROUTE_API, $submissionContext->getPath(), 'temporaryFiles');

		$chaptersGridUrl = $request->getDispatcher()->url(
			$request,
			ROUTE_COMPONENT,
			null,
			'grid.users.chapter.ChapterGridHandler',
			'fetchGrid',
			$submission->getId(),
			[
				'submissionId' => $submission->getId(),
				'publicationId' => '__publicationId__',
			]
		);

		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($submissionContext->getId());

		$audienceForm = new APP\components\forms\submission\AudienceForm($submissionApiUrl, $submission);
		$catalogEntryForm = new APP\components\forms\publication\CatalogEntryForm($latestPublicationApiUrl, $locales, $latestPublication, $submission, $baseUrl, $temporaryFileApiUrl);

		$templateMgr->setConstants([
			'FORM_AUDIENCE',
			'FORM_CATALOG_ENTRY',
			'WORK_TYPE_AUTHORED_WORK',
			'WORK_TYPE_EDITED_VOLUME',
		]);

		$workflowData = $templateMgr->getTemplateVars('workflowData');
		$workflowData['chaptersGridUrl'] = $chaptersGridUrl;
		$workflowData['components'][FORM_AUDIENCE] = $audienceForm->getConfig();
		$workflowData['components'][FORM_CATALOG_ENTRY] = $catalogEntryForm->getConfig();
		$workflowData['i18n']['editedVolume'] = __('submission.workflowType.editedVolume.label');
		$workflowData['i18n']['monograph'] = __('common.publication');

		$templateMgr->assign('workflowData', $workflowData);
	}


	//
	// Protected helper methods
	//
	/**
	 * Return the editor assignment notification type based on stage id.
	 * @param $stageId int
	 * @return int
	 */
	protected function getEditorAssignmentNotificationTypeByStageId($stageId) {
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
	protected function _getRepresentationsGridUrl($request, $submission) {
		return $request->getDispatcher()->url(
			$request,
			ROUTE_COMPONENT,
			null,
			'grid.catalogEntry.PublicationFormatGridHandler',
			'fetchGrid',
			$submission->getId(),
			[
				'submissionId' => $submission->getId(),
				'publicationId' => '__publicationId__',
			]
		);
	}
}
