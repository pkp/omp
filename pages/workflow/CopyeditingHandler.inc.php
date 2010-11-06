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
	 **/
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
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Show the copyediting page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function copyediting(&$args, &$request) {
		$this->setupTemplate();
		$monographId = array_shift($args);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		$templateMgr =& TemplateManager::getManager();

		// Grid actions
		$actionArgs = array('monographId' => $monographId);

		// import action class
		import('lib.pkp.classes.linkAction.LinkAction');
		$dispatcher =& $this->getDispatcher();

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
		$templateMgr->assign('monograph', $monograph);
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->display('seriesEditor/copyediting.tpl');
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));
		parent::setupTemplate();
	}
}
?>
