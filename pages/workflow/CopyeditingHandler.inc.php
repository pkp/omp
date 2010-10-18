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

// $Id$

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.submission.common.Action');

class CopyeditingHandler extends Handler {
	/**
	 * Constructor
	 **/
	function CopyeditingHandler() {
		parent::Handler();
	}

	function copyediting(&$args, &$request) {
		$this->setupTemplate();
		$monographId = array_shift($args);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		$templateMgr =& TemplateManager::getManager();

		// Grid actions
		$actionArgs = array('monographId' => $monographId);

		// import action class
		import('linkAction.LinkAction');
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
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));
		parent::setupTemplate();
	}
}
?>
