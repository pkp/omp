<?php

/**
 * @file controllers/grid/files/submission/EditorSubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorSubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests on the editor's submission details pages.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class EditorSubmissionDetailsFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function EditorSubmissionDetailsFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_SUBMISSION);
		parent::FileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_SUBMISSION,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles')
		);

		// Grid title.
		$this->setTitle('submission.submit.submissionFiles');
	}
}

?>
