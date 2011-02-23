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

// import submission files grid specific classes
import('controllers.grid.files.submission.SubmissionDetailsFilesGridHandler');

class EditorSubmissionDetailsFilesGridHandler extends SubmissionDetailsFilesGridHandler {
	/**
	 * Constructor
	 */
	function EditorSubmissionDetailsFilesGridHandler() {
		parent::SubmissionDetailsFilesGridHandler(true, false, true);
	}

}