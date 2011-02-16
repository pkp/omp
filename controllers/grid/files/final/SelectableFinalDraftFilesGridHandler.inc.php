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


// Import submission files grid base class
import('controllers.grid.files.final.FinalDraftFilesGridHandler');

class SelectableFinalDraftFilesGridHandler extends FinalDraftFilesGridHandler {
	/** @var boolean */
	var $_canManage;

	/**
	 * Constructor
	 */
	function SelectableFinalDraftFilesGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = true, $canManage = true) {
		$this->_canManage = $canManage;
		parent::FinalDraftFilesGridHandler(true, true, false, false);
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
		// Set the files to all the available files (submission and final draft file types).
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId());
		$rowData = array();
		$selectedFileIds = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] =& $monographFile;

			if($monographFile->getFileStage() == MONOGRAPH_FILE_FINAL) {
				$selectedFileIds[] = $monographFile->getFileId() . "-" . $monographFile->getRevision();
			}
			unset($monographFile);
		}
		$this->setData($rowData);
		$this->setSelectedFileIds($selectedFileIds);
	}

}