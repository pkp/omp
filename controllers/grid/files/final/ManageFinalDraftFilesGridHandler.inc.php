<?php

/**
 * @file controllers/grid/files/final/ManageFinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageReviewFilesGridHandler
 * @ingroup controllers_grid_files_final
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review or to next review round)
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class ManageFinalDraftFilesGridHandler extends SelectableFileListGridHandler {
	/**
	 * Constructor
	 */
	function ManageFinalDraftFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::SelectableFileListGridHandler(
			new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_FINAL),
			WORKFLOW_STAGE_ID_EDITING,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
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
				'downloadFile',
				'deleteFile',
				'updateFinalDraftFiles'
			)
		);

		// Set the grid title.
		$this->setTitle('submission.finalDraft');
	}


	//
	// Public handler methods
	//
	/**
	 * Save 'manage final draft files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFinalDraftFiles($args, &$request) {
		$monograph =& $this->getMonograph();

		import('controllers.grid.files.final.form.ManageFinalDraftFilesForm');
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph->getId());
		$manageFinalDraftFilesForm->readInputData();

		if ($manageFinalDraftFilesForm->validate()) {
			$manageFinalDraftFilesForm->execute($args, $request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}


	//
	// Overridden protected methods from SelectableFileListGridHandler
	//
	/**
	 * @see SelectableFileListGridHandler::getSelectedFileIds
	 */
	function getSelectedFileIds($submissionFiles) {
		// By default, select all files.
		$submissionFileIds = array();
		foreach($submissionFiles as $fileData) {
			$file =& $fileData['submissionFile'];
			if ($file->getViewable()) {
				$submissionFileIds[] = $file->getFileIdAndRevision();
			}
			unset($file);
		}
		return $submissionFileIds;
	}
}

?>
