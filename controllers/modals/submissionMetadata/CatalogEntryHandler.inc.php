<?php

/**
 * @file controllers/modals/submissionMetadata/CatalogEntryHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle requests for a submission's catalog entry.
 */

import('classes.controllers.modals.submissionMetadata.SubmissionMetadataHandler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class CatalogEntryHandler extends SubmissionMetadataHandler {
	/**
	 * Constructor.
	 */
	function CatalogEntryHandler() {
		parent::SubmissionMetadataHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'saveForm'));
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
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>
