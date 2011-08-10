<?php

/**
 * @file controllers/grid/files/final/SelectableFinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableFinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_final
 *
 * @brief Handle the final draft files grid (displays files sent to copyediting from the review stage)
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class SelectableFinalDraftFilesGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableFinalDraftFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::SelectableFileListGridHandler(
			new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_FINAL),
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

		// Set the grid title
		$this->setTitle('submission.finalDraft');
	}

	/**
	 * @see SelectableFileListGridHandler::getSelectedFileIds
	 * @return array
	 */
	function getSelectedFileIds($submissionFiles) {
		// By default, select all files.
		$submissionFileIds = array();
		foreach($submissionFiles as $fileData) {
			$file =& $fileData['submissionFile'];
			$submissionFileIds[] = $file->getFileIdAndRevision();
			unset($file);
		}
		return $submissionFileIds;
	}
}

?>
