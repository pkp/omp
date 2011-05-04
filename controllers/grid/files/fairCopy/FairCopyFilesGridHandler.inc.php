<?php

/**
 * @file controllers/grid/files/fairCopy/FairCopyFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FairCopyFilesGridHandler
 * @ingroup controllers_grid_files_fairCopy
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class FairCopyFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(
			WORKFLOW_STAGE_ID_EDITING,
			MONOGRAPH_FILE_FAIR_COPY
		);
		parent::FileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_EDITING,
			FILE_GRID_ADD|FILE_GRID_DELETE
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

		$this->setTitle('submission.fairCopy');
	}
}

?>
