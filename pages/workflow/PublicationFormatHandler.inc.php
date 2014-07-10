<?php

/**
 * @file pages/workflow/PublicationFormatHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatHandler
 * @ingroup controllers_template_workflow
 *
 * @brief Publication format sub-page handler
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatHandler extends Handler {
	/**
	 * Constructor
	 */
	function PublicationFormatHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
			array('fetchPublicationFormat')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		// Get the publication Format Policy
		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$publicationFormatPolicy = new PublicationFormatRequiredPolicy($request, $args);

		// Get the workflow stage policy
		import('classes.security.authorization.WorkflowStageAccessPolicy');
		$stagePolicy = new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', WORKFLOW_STAGE_ID_PRODUCTION);

		// Add the Publication Format policy to the stage policy.
		$stagePolicy->addPolicy($publicationFormatPolicy);

		// Add the augmented policy to the handler.
		$this->addPolicy($stagePolicy);
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $args) {
		$this->setupTemplate($request);
	}

	/**
	 * Setup variables for the template
	 * @param $request Request
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_APP_EDITOR);

		$templateMgr = TemplateManager::getManager($request);

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		// Assign the authorized monograph.
		$templateMgr->assign_by_ref('submission', $monograph);
		$templateMgr->assign('stageId', $stageId);
		$templateMgr->assign_by_ref('publicationFormat', $publicationFormat);
	}


	//
	// Public operations
	//
	/**
	 * Display the publication format template (grid + actions).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetchPublicationFormat($args, $request) {
		// Fetch the template
		$templateMgr = TemplateManager::getManager($request);
		return $templateMgr->fetchJson('controllers/tab/workflow/publicationFormat.tpl');
	}
}

?>
