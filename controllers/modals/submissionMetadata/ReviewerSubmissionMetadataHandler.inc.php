<?php

/**
 * @file controllers/modals/submissionMetadata/ReviewerSubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionMetadataHandler
 * @ingroup controllers_modals_reviewierSubmissionMetadata
 *
 * @brief Display submission metadata to reviewers
 */

import('controllers.modals.submissionMetadata.SubmissionMetadataHandler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class ReviewerSubmissionMetadataHandler extends SubmissionMetadataHandler {
	/**
	 * Constructor.
	 */
	function ReviewerSubmissionMetadataHandler() {
		parent::SubmissionMetadataHandler();

		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT), array());
		$this->addRoleAssignment(array(ROLE_ID_REVIEWER), array('fetch'));
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}
?>