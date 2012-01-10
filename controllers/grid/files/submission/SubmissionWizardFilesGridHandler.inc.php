<?php

/**
 * @file controllers/grid/files/submission/SubmissionWizardFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionWizardFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests at the author submission wizard.
 * The submission author and all press/editor roles have access to this grid.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class SubmissionWizardFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function SubmissionWizardFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::FileListGridHandler(new SubmissionFilesGridDataProvider(
			MONOGRAPH_FILE_SUBMISSION),
			WORKFLOW_STAGE_ID_SUBMISSION,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
		);
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow')
		);

		// Set grid title.
		$this->setTitle('submission.submit.submissionFiles');
	}
}

?>
