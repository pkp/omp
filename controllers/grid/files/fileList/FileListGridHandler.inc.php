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
	/** @var boolean */
	var $_canManage;


	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $canAdd boolean whether the grid will contain
	 *  an "add file" button.
	 * @param $isSelectable boolean whether this grid displays
	 *  checkboxes on each grid row that allows files to be selected
	 *  as form inputs
	 * @param $canDownloadAll boolean whether the user can download
	 *  all files in the grid as a compressed file
	 * @param $canManage boolean whether the grid shows a "manage files"
	 *  action.
	 */
	function FileListGridHandler($dataProvider, $canAdd = true, $isSelectable = false, $canDownloadAll = false, $canManage = false) {
		$this->_canManage = $canManage;

		parent::SubmissionFilesGridHandler($dataProvider, $canAdd, $isSelectable, $canDownloadAll);
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