<?php
/**
 * @file classes/controllers/grid/files/fileList/FileListGridHandlerImplementation.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileListGridHandlerImplementation
 * @ingroup controllers_grid_files_fileList
 *
 * @brief This class implements some of the common behaviours and data that a grid handler
 * can use to handle with a submission files list.
 */
// Import base class.
import('classes.controllers.grid.files.SubmissionFilesGridHandlerImplementation');

// Define file grid capabilities.
define('FILE_GRID_MANAGE',		0x00000010);

class FileListGridHandlerImplementation extends SubmissionFilesGridHandlerImplementation {

	/** @var boolean */
	var $_canManage;

	/**
	 * Constructor
	 * @param $gridHandler Handler The handler that instantiated this class.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileListGridHandlerImplementation(&$gridHandler, $stageId, $capabilities) {
		$this->_canManage = (boolean)($capabilities & FILE_GRID_MANAGE);

		parent::SubmissionFilesGridHandlerImplementation($gridHandler, $stageId, $capabilities);
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
	// Public methods
	//
	/**
	 * @see SubmissionFilesGridHandlerImplementation::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$gridHandler =& $this->getGridHandler();

		// Add the "manage files" action if required.
		if($this->canManage()) {
			$dataProvider =& $gridHandler->getDataProvider();
			$gridHandler->addAction($dataProvider->getSelectAction($request));
		}

		// The file list grid layout has an additional file genre column.
		import('controllers.grid.files.fileList.FileGenreGridColumn');
		$gridHandler->addColumn(new FileGenreGridColumn());
	}
}

?>
