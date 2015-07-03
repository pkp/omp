<?php

/**
 * @file controllers/tab/workflow/ReviewRoundTabHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundTabHandler
 * @ingroup controllers_tab_workflow
 *
 * @brief Handle AJAX operations for review round tabs on review stages workflow pages.
 */

import('classes.handler.Handler');

// Import the base class.
import('lib.pkp.classes.controllers.tab.workflow.PKPReviewRoundTabHandler');

class ReviewRoundTabHandler extends PKPReviewRoundTabHandler {

	/**
	 * Constructor
	 */
	function ReviewRoundTabHandler() {
		parent::PKPReviewRoundTabHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
			array('internalReviewRound', 'externalReviewRound')
		);
	}


	//
	// Extended methods from Handler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId'); // This is validated in WorkflowStageAccessPolicy.

		import('classes.security.authorization.WorkflowStageAccessPolicy');
		$this->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * JSON fetch the internal review round info (tab).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function internalReviewRound($args, $request) {
		return $this->_reviewRound($args, $request);
	}
}

?>
