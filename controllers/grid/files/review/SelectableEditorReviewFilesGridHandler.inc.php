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

import('controllers.grid.files.review.EditorReviewFilesGridHandler');

class SelectableEditorReviewFilesGridHandler extends EditorReviewFilesGridHandler {

	/**
	 * Constructor
	 */
	function SelectableEditorReviewFilesGridHandler() {
		parent::EditorReviewFilesGridHandler(true, true, false);
	}


	//
	// Protected methods
	//
	/**
	 * Select the files to load in the grid
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$monograph =& $this->getMonograph();

		// Set the files to all the available files to allow selection.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId());
		$this->setData($monographFiles);

		// Set the already selected elements of the grid (the current review files).
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$selectedRevisions =& $reviewRoundDao->getReviewFilesAndRevisionsByRound($monograph->getId(), $this->getRound(), true);
		$this->setSelectedFileIds($selectedRevisions);
	}
}