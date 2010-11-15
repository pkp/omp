<?php

/**
 * @file pages/workflow/CopyeditingHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the copyediting stage of the submssion workflow.
 */


import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.submission.common.Action');

class CopyeditingHandler extends Handler {
	/**
	 * Constructor
	 */
	function CopyeditingHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('copyediting'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));
		parent::setupTemplate();
	}


	//
	// Public handler methods
	//
	/**
	 * Show the copyediting page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function copyediting(&$args, &$request) {
		// Set up the view.
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();

		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign('monograph', $monograph);
		$templateMgr->assign('monographId', $monograph->getId());

		// Grid actions
		$actionArgs = array('monographId' => $monograph->getId());
		$dispatcher =& $this->getDispatcher();

		import('lib.pkp.classes.linkAction.LinkAction');
		$promoteAction =& new LinkAction(
			'sendToProduction',
			LINK_ACTION_MODE_CONFIRM,
			null,
			$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', 'sendToProduction', null, $actionArgs),
			'editor.monograph.decision.sendToProduction',
			null,
			null,
			Locale::translate('editor.monograph.decision.sendToProduction.confirm')
		);
		$templateMgr->assign('promoteAction', $promoteAction);

		// Render the view.
		$templateMgr->display('seriesEditor/copyediting.tpl');
	}
}
?>
