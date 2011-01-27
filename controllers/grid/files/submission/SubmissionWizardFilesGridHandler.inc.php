<?php

/**
 * @file controllers/grid/files/submission/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests at the author submission wizard.
 * The submission author and all press/editor roles have access to this grid.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.SubmissionFilesGridHandler');

class SubmissionWizardFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function SubmissionWizardFilesGridHandler() {
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_SUBMISSION, true);

		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'finishFileSubmission', 'addFile', 'displayFileUploadForm', 'uploadFile', 'confirmRevision',
						'editMetadata', 'saveMetadata', 'downloadFile', 'downloadAllFiles', 'deleteFile'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Basic grid configuration
		$this->setTitle('submission.submit.submissionFiles');

		// Load monograph files.
		$this->loadMonographFiles();

		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		$this->addColumn(new GridColumn('fileType',	'common.fileType', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}
}