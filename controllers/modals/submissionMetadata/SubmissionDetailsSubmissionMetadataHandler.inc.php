<?php

/**
 * @file controllers/modals/submissionMetadata/SubmissionHeaderSubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHeaderSubmissionMetadataHandler
 * @ingroup controllers_modals_submissionHeaderSubmissionMetadata
 *
 * @brief Handle requests for non-reviewers to see a submission's metadata
 */

import('controllers.modals.submissionMetadata.SubmissionMetadataHandler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class SubmissionHeaderSubmissionMetadataHandler extends SubmissionMetadataHandler {
	/**
	 * Constructor.
	 */
	function SubmissionHeaderSubmissionMetadataHandler() {
		parent::SubmissionMetadataHandler();

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
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}
}
?>