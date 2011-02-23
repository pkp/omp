<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridDataProvider
 * @ingroup controllers_grid_files
 *
 * @brief Provide access to submission file data for grids.
 */


import('controllers.grid.files.FilesGridDataProvider');

class SubmissionFilesGridDataProvider extends FilesGridDataProvider {

	/** @var integer */
	var $_fileStage;


	/**
	 * Constructor
	 */
	function SubmissionFilesGridDataProvider($fileStage) {
		assert(is_numeric($fileStage) && $fileStage > 0);
		$this->_fileStage = (int)$fileStage;
		parent::FilesGridDataProvider();
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$policy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', MonographFile::fileStageToWorkflowStage($this->_getFileStage()));
		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
				'monographId' => $monograph->getId(),
				'fileStage' => $this->_getFileStage());
	}

	/**
	 * @see GridDataProvider::getRowData()
	 */
	function getRowData() {
		// Retrieve all monograph files for the given file stage.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->_getFileStage());

		// Rearrange the files by file id as required by the grid.
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}

		return $rowData;
	}


	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddFileLinkAction($request, $monograph->getId(), $this->_getFileStage());
		return $addFileAction;
	}


	//
	// Private helper methods
	//
	/**
	 * Get the file stage.
	 * @return integer
	 */
	function _getFileStage() {
	    return $this->_fileStage;
	}
}