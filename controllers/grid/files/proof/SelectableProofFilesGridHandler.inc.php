<?php

/**
 * @file controllers/grid/files/submission/SelectableProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableProofFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle selecting proofs from a publication format
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class SelectableProofFilesGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 */
	function SelectableProofFilesGridHandler() {
		import('controllers.grid.files.proof.ProofFilesGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::SelectableFileListGridHandler(
			new ProofFilesGridDataProvider(),
			null,
			FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT ),
			array('fetchGrid', 'fetchRow')
		);

		// Set the grid title.
		$this->setTitle('editor.monograph.proofs');
	}

	/**
	 * Get the already-available proof file IDs.
	 * @return array(int $fileId, int $fileId, ...)
	 */
	function getSelectedFileIds($submissionFiles) {
		$selectedFileIds = array();
		foreach ($submissionFiles as $id => $submissionFileData) {
			$submissionFile =& $submissionFileData['submissionFile'];
			if ($submissionFile->getViewable()) {
				$selectedFileIds[] = $id;
			}
			unset($submissionFile);
		}
		return $selectedFileIds;
	}
}

?>
