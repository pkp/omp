<?php

/**
 * @file controllers/timeline/TimelineHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TimelineHandler
 * @ingroup controllers_timeline
 *
 * @brief Submission timeline controller
 */

import('classes.handler.Handler');

class TimelineHandler extends Handler {
	/**
	 * Constructor
	 */
	function TimelineHandler() {
		parent::Handler();

		// Author can do everything except delete notes.
		// (Review-related log entries are hidden from the author, but
		// that's not implemented here.)
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('fetch')
		);
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = $request->getUservar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public operations
	//
	/**
	 * Display the submission timeline.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetch($args, $request) {
		// Load locale components
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Give the monograph to the template
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $monograph->getStageId());

		// Generate & return component contents
		return $templateMgr->fetchJson('controllers/timeline/index.tpl');
	}
}

?>
