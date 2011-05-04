<?php

/**
 * @file controllers/grid/files/final/FinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_final
 *
 * @brief Handle the final draft files grid (displays files sent to copyediting from the review stage)
 */


import('controllers.grid.files.fileList.FileListGridHandler');

class FinalDraftFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FinalDraftFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(
			WORKFLOW_STAGE_ID_EDITING,
			MONOGRAPH_FILE_FINAL
		);
		parent::FileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_EDITING,
			FILE_GRID_DOWNLOAD_ALL|FILE_GRID_ADD|FILE_GRID_DELETE
		);
		$this->addRoleAssignment(
			array(
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow',
				'addFile',
				'downloadFile', 'downloadAllFiles',
				'deleteFile'
			)
		);

		$this->setTitle('submission.finalDraft');
	}
}

?>
