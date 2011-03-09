<?php

/**
 * @file controllers/grid/files/submission/SelectableSubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableSubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests in the editor's 'approve submission' modal.
 */

// import submission files grid specific classes
import('controllers.grid.files.submission.SubmissionDetailsFilesGridHandler');

class SelectableSubmissionDetailsFilesGridHandler extends SubmissionDetailsFilesGridHandler {
	/**
	 * Constructor
	 */
	function SelectableSubmissionDetailsFilesGridHandler() {
		parent::SubmissionDetailsFilesGridHandler(
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL|FILE_GRID_MANAGE
		);
	}

}