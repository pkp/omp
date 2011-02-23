<?php
/**
 * @defgroup controllers_grid_files_fileList
 */

/**
 * @file controllers/grid/files/fileList/FileListGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileListGridHandler
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Base grid for simple file lists. This grid shows the file type in
 *  addition to the file name.
 */

import('controllers.grid.files.SubmissionFilesGridHandler');

class FileListGridHandler extends SubmissionFilesGridHandler {

	/**
	 * Constructor
	 * @see SubmissionFilesGridHandler::SubmissionFilesGridHandler()
	 */
	function FileListGridHandler($fileStage, $canAdd = true, $isSelectable = false, $canDownloadAll = false) {
		parent::SubmissionFilesGridHandler($fileStage, $canAdd, $isSelectable, $canDownloadAll);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// The file list grid layout has an additional file genre column.
		import('controllers.grid.files.fileList.FileGenreGridColumn');
		$this->addColumn(new FileGenreGridColumn());
	}
}