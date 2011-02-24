<?php

/**
 * @file controllers/grid/files/review/SelectableEditorReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableEditorReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review or to next review round)
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class SelectableEditorReviewFilesGridHandler extends SelectableFileListGridHandler {

	/** @var array */
	var $_selectionArgs;


	/**
	 * Constructor
	 */
	function SelectableEditorReviewFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_SUBMISSION);
		parent::SelectableFileListGridHandler($dataProvider, true, true, false);

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'updateReviewFiles'));

		// Set the grid title.
		$this->setTitle('reviewer.monograph.reviewFiles');
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

		import('controllers.grid.files.review.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monograph->getId(), $this->getSelectionArg('reviewType'), $this->getSelectionArg('round'));

		$manageReviewFilesForm->readInputData();

		if ($manageReviewFilesForm->validate()) {
			$manageReviewFilesForm->execute($args, $request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}


	//
	// Overridden protected methods from SelectableFileListGridHandler
	//
	/**
	 * @see SelectableFileListGridHandler::getSelectionPolicy()
	 */
	function getSelectionPolicy(&$request, $args, $roleAssignments) {
		// FIXME: Authorize review assignment, see #6200.
		// Retrieve the authorized selection.
		$this->_selectionArgs = array(
			'reviewType' => (int)$request->getUserVar('reviewType'),
			'round' => (int)$request->getUserVar('round'));
		return null;
	}

	/**
	 * @see SelectableFileListGridHandler::getSelectionArgs()
	 */
	function getSelectionArgs() {
		return $this->_selectionArgs;
	}

	/**
	 * @see SelectableFileListGridHandler::getSelectedFileIds()
	 */
	function getSelectedFileIds() {
		// Set the already selected elements of the grid (the current review files).
		$monograph =& $this->getMonograph();
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$selectedRevisions =& $reviewRoundDao->getReviewFilesAndRevisionsByRound($monograph->getId(), $this->getSelectionArg('round'), true);
		return $selectedRevisions;
	}
}