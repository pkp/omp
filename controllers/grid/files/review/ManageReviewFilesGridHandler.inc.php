<?php

/**
 * @file controllers/grid/files/review/ManageReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review or to next review round)
 */

import('controllers.grid.files.fileList.SelectableFileListGridHandler');

class ManageReviewFilesGridHandler extends SelectableFileListGridHandler {

	/** @var array */
	var $_selectionArgs;


	/**
	 * Constructor
	 */
	function ManageReviewFilesGridHandler() {
		import('controllers.grid.files.review.ReviewGridDataProvider');
		// Pass in null stageId to be set in initialize from request var.
		parent::SelectableFileListGridHandler(
			new ReviewGridDataProvider(MONOGRAPH_FILE_REVIEW_FILE),
			null,
			FILE_GRID_ADD|FILE_GRID_DOWNLOAD_ALL|FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'updateReviewFiles')
		);

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
		$manageReviewFilesForm = new ManageReviewFilesForm($monograph->getId(), $this->getRequestArg('stageId'), $this->getRequestArg('reviewRoundId'));
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
	// Extended methods from SelectableFileListGridHandler
	//
	/**
	 * @see SelectableFileListGridHandler::initialize()
	 */
	function initialize(&$request) {
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		$this->_selectionArgs = array(
					'stageId' => $stageId,
					'round' => $reviewRound->getRound(),
					'reviewRoundId' => $reviewRound->getId()
		);

		parent::initialize($request);
	}


	//
	// Overridden protected methods from SelectableFileListGridHandler
	//
	/**
	 * @see SelectableFileListGridHandler::getSelectionArgs()
	 */
	function getSelectionArgs() {
		return $this->_selectionArgs;
	}

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

		// Include only the files marked viewable
		foreach ($selectedRevisions as $id => $revision) {
			if (!$revision->getViewable()) unset($selectedRevisions[$id]);
		}

		// Return the IDs
		return array_keys($selectedRevisions);
	}
}

?>
