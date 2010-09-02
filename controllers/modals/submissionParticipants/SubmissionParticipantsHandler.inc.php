<?php

/**
 * @file controllers/modals/submissionParticipants/SubmissionParticipantsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionParticipantsHandler
 * @ingroup controllers_modals_submissionParticipants
 *
 * @brief Handle requests for editors to make a decision
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class SubmissionParticipantsHandler extends Handler {
	/**
	 * Constructor.
	 */
	function SubmissionParticipantsHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch'));
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Display the submission participants grid
	 * @return JSON
	 */
	function fetch(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.modals.submissionParticipants.form.SubmissionParticipantsForm');
		$submissionParticipantsForm = new SubmissionParticipantsForm($monographId);
		$submissionParticipantsForm->initData($args, $request);

		$json = new JSON('true', $submissionParticipantsForm->fetch($request));
		return $json->getString();
	}

}
?>