<?php

/**
 * @file controllers/grid/files/submission/SubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Base handler for the submission stage grids.
 */

// Import the grid layout.
import('lib.pkp.controllers.grid.files.fileList.FileListGridHandler');

class SubmissionDetailsFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionDetailsFilesGridHandler($capabilities = 0) {
		import('lib.pkp.controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(SUBMISSION_FILE_SUBMISSION);
		parent::FileListGridHandler($dataProvider, WORKFLOW_STAGE_ID_SUBMISSION, $capabilities);
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $additionalActionArgs = array()) {
		// Basic grid configuration
		$this->setTitle('submission.submit.submissionFiles');

		parent::initialize($request);
	}
}

?>
