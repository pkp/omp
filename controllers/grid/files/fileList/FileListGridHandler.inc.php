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

// Define file grid capabilities.
define('FILE_GRID_MANAGE', 0x08);

class FileListGridHandler extends SubmissionFilesGridHandler {
	/** @var boolean */
	var $_canManage;


	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileListGridHandler($dataProvider, $stageId, $capabilities) {
		$this->_canManage = (boolean)($capabilities & FILE_GRID_MANAGE);

		parent::SubmissionFilesGridHandler($dataProvider, $stageId, $capabilities);
	}


	//
	// Getters/Setters
	//
	/**
	 * Whether the grid allows file management (select existing files to add to grid)
	 * @return boolean
	 */
	function canManage() {
		return $this->_canManage;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Add the "manage files" action if required.
		if($this->canManage()) {
			$dataProvider =& $this->getDataProvider();
			$this->addAction($dataProvider->getSelectAction($request));
		}

		// The file list grid layout has an additional file genre column.
		import('controllers.grid.files.fileList.FileGenreGridColumn');
		$this->addColumn(new FileGenreGridColumn());
	}
}