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

	/** @var array */
	var $_selectionArgs;


	/**
	 * Constructor
	 */
	function ManageFinalDraftFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::SelectableFileListGridHandler(
			new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_FINAL),
			null,
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'updateFinalDraftFiles')
		);

		// Set the grid title.
		$this->setTitle('reviewer.monograph.finalDraftFiles');
	}


	//
	// Public handler methods
	//
	/**
	 * Save 'manage review files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateReviewFiles($args, &$request) {
		$monograph =& $this->getMonograph();

		import('controllers.grid.files.final.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageFinalDraftFilesForm($monograph->getId());
		$manageReviewFilesForm->readInputData();

		if ($manageReviewFilesForm->validate()) {
			$manageReviewFilesForm->execute($args, $request);

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
	 * @see SelectableFileListGridHandler::getSelectedFileIds()
	 */
	function getSelectedFileIds($submissionFiles) {
		// Set the already selected elements of the grid (the current review files).
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$selectedRevisions =& $submissionFileDao->getRevisionsByReviewRound(
			$monograph->getId(),
			$this->getRequestArg('stageId'), $this->getRequestArg('round')
		);
		return array_keys($selectedRevisions);
	}
}

?>
