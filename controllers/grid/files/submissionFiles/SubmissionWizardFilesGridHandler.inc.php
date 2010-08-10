<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle submission file grid requests at the author submission wizard.
 * The submission author and all press/editor roles have access to this grid.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.submissionFiles.SubmissionFilesGridHandler');

class SubmissionWizardFilesGridHandler extends SubmissionFilesGridHandler {
	var $_monographId;

	/**
	 * Constructor
	 */
	function SubmissionWizardFilesGridHandler() {
		parent::SubmissionFilesGridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addFile', 'addRevision', 'editFile', 'displayFileForm', 'uploadFile',
				'confirmRevision', 'deleteFile', 'editMetadata', 'saveMetadata', 'finishFileSubmission',
				'returnFileRow', 'downloadFile'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionWizardMonographPolicy');
		$this->addPolicy(new OmpSubmissionWizardMonographPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}